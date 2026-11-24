<?php

declare(strict_types=1);

namespace Modules\Ebook\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserEbookLibrary;
use App\Support\EbookShop as Shop;
use App\Support\SchemaCache;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Author\Models\Author;
use Modules\Book\Models\Category;
use Modules\Ebook\Models\Ebook;
use Modules\Publisher\Models\Publisher;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * E-book shop: catalogue (/ebooks), book page, online reader, free sample, claim, download and reading progress.
 * Access and file rules: App\Support\EbookShop.
 */
class EbookController extends Controller
{
    private const PER_PAGE = 24;

    /** Shared eager loads and the review average for every card. */
    private function cards(): Builder
    {
        $q = Ebook::query()->with(['author:id,name,slug', 'category:id,name,slug'])->where('is_active', true);
        if (SchemaCache::has('reviews')) {
            $q->withAvg(['reviews' => fn ($r) => $r->where('is_approved', true)], 'rating')
              ->withCount(['reviews' => fn ($r) => $r->where('is_approved', true)]);
        }

        return $q;
    }

    private static function freeScope(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('price', '<=', 0)->orWhere('discount_price', '=', 0));
    }

    public function index(Request $request): View|JsonResponse
    {
        $ok = SchemaCache::has('ebooks');
        $user = auth()->user();
        $data = [
            'ebooks' => collect(), 'categories' => collect(), 'sidebarAuthors' => collect(), 'sidebarPublishers' => collect(),
            'shelves' => [], 'categoryShelves' => collect(), 'continueReading' => collect(), 'spotlightEbook' => null,
            'userLibraryIds' => [], 'total' => 0, 'isSearchMode' => false, 'activeFilterTitle' => null,
        ];

        // search box suggestions (JSON)
        if ($ok && ($request->ajax() || $request->wantsJson()) && $request->anyFilled(['q', 'search'])) {
            $term = trim((string) ($request->input('q') ?: $request->input('search')));
            if (mb_strlen($term) < 2) {
                return response()->json(['results' => []]);
            }
            $rows = $this->cards()->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('author_name', 'like', "%{$term}%")
                ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$term}%")))->take(8)->get();

            return response()->json(['results' => $rows->map(fn ($e) => [
                'title' => $e->title, 'author' => $e->author?->name ?: $e->author_name, 'cover_url' => $e->cover_url,
                'price' => Shop::price($e), 'is_free' => $e->is_free, 'url' => route('ebook.show', $e->slug ?: $e->id),
            ])]);
        }

        if (!$ok) {
            return view('ebook::frontend.index', $data);
        }

        $filterKeys = ['search', 'q', 'category', 'author', 'publisher', 'format', 'discount_min', 'sort'];
        $data['isSearchMode'] = $request->anyFilled($filterKeys) || (int) $request->input('page', 1) > 1;
        $data['total'] = Cache::remember('ebookshop:total', 300, fn () => Ebook::where('is_active', true)->count());

        if ($user && SchemaCache::has('user_ebook_library')) {
            $data['userLibraryIds'] = UserEbookLibrary::where('user_id', $user->id)->where('is_active', true)->pluck('ebook_id')->all();
        }

        // filter menus (counts of active e-books only), cached briefly: the same for every visitor
        [$data['categories'], $data['sidebarAuthors'], $data['sidebarPublishers']] = Cache::remember('ebookshop:menus', 300, function () {
            $active = fn ($q) => $q->where('is_active', true);
            $cats = SchemaCache::has('categories') ? Category::where('is_active', true)->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->where('is_active', true)->withCount(['ebooks' => $active])])
                ->withCount(['ebooks' => $active])->orderBy('sort_order')->orderByDesc('ebooks_count')->get()
                ->filter(fn ($c) => $c->ebooks_count > 0 || $c->children->sum('ebooks_count') > 0)->values() : collect();
            $authors = SchemaCache::has('authors') ? Author::where('is_active', true)->whereHas('ebooks', $active)
                ->withCount(['ebooks' => $active])->orderByDesc('ebooks_count')->orderBy('name')->take(60)->get(['id', 'name', 'slug']) : collect();
            $pubs = SchemaCache::has('publishers') ? Publisher::where('is_active', true)->whereHas('ebooks', $active)
                ->withCount(['ebooks' => $active])->orderByDesc('ebooks_count')->orderBy('name')->take(60)->get(['id', 'name', 'slug']) : collect();

            return [$cats, $authors, $pubs];
        });

        if (!$data['isSearchMode']) {
            // shelves on the shop front
            $new = $this->cards()->latest('id')->take(15)->get();
            $best = $this->cards()->orderByDesc('sales_count')->orderByDesc('read_count')->latest('id')->take(15)->get();
            $sale = $this->cards()->whereNotNull('discount_price')->where('discount_price', '>', 0)->whereColumn('discount_price', '<', 'price')->latest('id')->take(15)->get();
            $free = self::freeScope($this->cards())->latest('id')->take(15)->get();
            $data['shelves'] = [
                ['Bestsellers', $best, route('ebook.index', ['sort' => 'bestselling']), 'ebBest'],
                ['New', $new, route('ebook.index', ['sort' => 'latest']), 'ebNew'],
                ['Sale', $sale, route('ebook.index', ['discount_min' => 1]), 'ebSale'],
                ['Free', $free, route('ebook.index', ['format' => 'free']), 'ebFree'],
            ];
            $data['spotlightEbook'] = $best->first(fn ($e) => $e->cover_url) ?: $new->first();

            // one row per category, one query for all rows
            $top = $data['categories']->take(12);
            if ($top->isNotEmpty()) {
                $owner = [];
                foreach ($top as $c) {
                    $owner[$c->id] = $c->id;
                    foreach ($c->children as $ch) $owner[$ch->id] = $c->id;
                }
                $books = $this->cards()->whereIn('category_id', array_keys($owner))->latest('id')->take(400)->get()->groupBy(fn ($e) => $owner[$e->category_id] ?? 0);
                $data['categoryShelves'] = $top->map(function ($c) use ($books) {
                    $c->setRelation('shelf', ($books->get($c->id) ?? collect())->take(12)->values());
                    return $c;
                })->filter(fn ($c) => $c->shelf->count() >= 2)->values();
            }

            // continue reading (signed-in readers)
            if ($user && SchemaCache::has('user_ebook_library')) {
                $rows = UserEbookLibrary::where('user_id', $user->id)->where('progress_percent', '>', 0)->where('progress_percent', '<', 100)
                    ->latest('updated_at')->take(10)->get(['ebook_id', 'progress_percent']);
                if ($rows->isNotEmpty()) {
                    $books = $this->cards()->whereIn('id', $rows->pluck('ebook_id'))->get()->keyBy('id');
                    $data['continueReading'] = $rows->map(function ($r) use ($books) {
                        $b = $books->get($r->ebook_id);
                        if ($b) $b->setAttribute('reading_progress', (int) $r->progress_percent);
                        return $b;
                    })->filter()->values();
                }
            }

            return view('ebook::frontend.index', $data);
        }

        // ── catalogue with filters ──
        $q = $this->cards();
        $title = null;

        if ($request->filled('category')) {
            $val = trim((string) $request->input('category'));
            $cat = Category::where('slug', $val)->orWhere('id', ctype_digit($val) ? (int) $val : 0)->first();
            if ($cat) {
                $q->whereIn('category_id', array_merge([$cat->id], $cat->children()->pluck('id')->all()));
                $title = $cat->name;
            } else {
                $q->whereRaw('1 = 0');
            }
        }
        if ($request->filled('author')) {
            $val = trim((string) $request->input('author'));
            $a = Author::where('slug', $val)->orWhere('id', ctype_digit($val) ? (int) $val : 0)->first();
            $q->where(fn ($w) => $a ? $w->where('author_id', $a->id)->orWhere('author_name', $a->name) : $w->where('author_name', 'like', "%{$val}%"));
            $title ??= $a?->name;
        }
        if ($request->filled('publisher')) {
            $val = trim((string) $request->input('publisher'));
            $p = Publisher::where('slug', $val)->orWhere('id', ctype_digit($val) ? (int) $val : 0)->first();
            $p ? $q->where('publisher_id', $p->id) : $q->whereRaw('1 = 0');
            $title ??= $p?->name;
        }
        match (strtolower((string) $request->input('format'))) {
            'epub' => $q->where(fn ($w) => $w->whereNotNull('epub_file_path')->where('epub_file_path', '!=', '')->orWhere('file_type', 'epub')->orWhere('file_path', 'like', '%.epub')),
            'pdf'  => $q->where(fn ($w) => $w->where('file_type', 'pdf')->orWhere('file_path', 'like', '%.pdf')),
            'free' => self::freeScope($q),
            'paid' => $q->where('price', '>', 0)->where(fn ($w) => $w->whereNull('discount_price')->orWhere('discount_price', '>', 0)),
            default => null,
        };
        if ((int) $request->input('discount_min') > 0) {
            $q->whereNotNull('discount_price')->where('discount_price', '>', 0)->whereColumn('discount_price', '<', 'price');
            $title ??= 'Sale';
        }
        $term = trim((string) ($request->input('search') ?: $request->input('q')));
        if ($term !== '') {
            $words = array_slice(array_filter(preg_split('/\s+/u', $term)), 0, 5);
            // every word must appear in the title, author, publisher, category or ISBN
            foreach ($words as $w) {
                $like = "%{$w}%";
                $q->where(fn ($s) => $s->where('title', 'like', $like)->orWhere('subtitle', 'like', $like)->orWhere('author_name', 'like', $like)
                    ->orWhere('isbn', 'like', $like)
                    ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $like))
                    ->orWhereHas('publisher', fn ($p) => $p->where('name', 'like', $like))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)));
            }
        }
        match ((string) $request->input('sort')) {
            'price_low'   => $q->orderByRaw('COALESCE(NULLIF(discount_price, 0), price) asc'),
            'price_high'  => $q->orderByRaw('COALESCE(NULLIF(discount_price, 0), price) desc'),
            'bestselling' => $q->orderByDesc('sales_count'),
            'popular'     => $q->orderByDesc('read_count'),
            'rating'      => SchemaCache::has('reviews') ? $q->orderByDesc('reviews_avg_rating') : $q->latest('id'),
            default       => $q->latest('id'),
        };

        $data['ebooks'] = $q->paginate(self::PER_PAGE)->withQueryString();
        $data['activeFilterTitle'] = $title;

        return view('ebook::frontend.index', $data);
    }

    public function show(string $slug): View
    {
        $ebook = $this->findOr404($slug);
        $user = auth()->user();
        $ebook->loadMissing(['author', 'publisher', 'category']);
        $reviews = SchemaCache::has('reviews')
            ? $ebook->reviews()->where('is_approved', true)->with('user:id,name')->latest('id')->take(50)->get()
            : collect();

        $owns = Shop::owns($user, $ebook);
        $entry = Shop::entry($user, $ebook);

        $related = $this->cards()->where('id', '!=', $ebook->id)
            ->when($ebook->category_id, fn ($q) => $q->where('category_id', $ebook->category_id))
            ->latest('id')->take(10)->get();
        $byAuthor = $ebook->author_id
            ? $this->cards()->where('id', '!=', $ebook->id)->where('author_id', $ebook->author_id)->latest('id')->take(10)->get()
            : collect();
        $related = $related->reject(fn ($r) => $byAuthor->contains('id', $r->id))->take(10)->values();

        $full = Shop::fullFile($ebook);

        return view('ebook::frontend.show', [
            'ebook'       => $ebook,
            'reviews'     => $reviews,
            'reviewCount' => $reviews->count(),
            'avgRating'   => $reviews->count() ? round((float) $reviews->avg('rating'), 1) : null,
            'owns'        => $owns,
            'canRead'     => Shop::canReadFull($user, $ebook),
            'isStaff'     => Shop::isStaff($user, $ebook),
            'entry'       => $entry,
            'hasFile'     => (bool) $full,
            'kind'        => Shop::kind($full),
            'hasSample'   => Shop::hasSample($ebook),
            'canDownload' => $owns && $full && Shop::kind($full) === 'epub',
            'price'       => Shop::price($ebook),
            'related'     => $related,
            'byAuthor'    => $byAuthor,
            'userReviewed'=> $user ? $reviews->contains('user_id', $user->id) : false,
        ]);
    }

    /** Online reader: the whole book for those who may read it, otherwise the sample. */
    public function read(string $slug): View|RedirectResponse
    {
        $ebook = $this->findOr404($slug);
        $user = auth()->user();
        if (!Shop::canReadFull($user, $ebook)) {
            return redirect()->route('ebook.preview', $ebook->slug ?: $ebook->id);
        }
        if (!Shop::fullFile($ebook)) {
            return redirect()->route('ebook.show', $ebook->slug ?: $ebook->id)->with('error', 'এই ই-বুকের ফাইল এখনো যুক্ত হয়নি। কিছুক্ষণ পর আবার চেষ্টা করুন।');
        }
        // one count per visitor per book per day
        if (Cache::add('ebook:read:' . $ebook->id . ':' . sha1((string) (auth()->id() ?: request()->ip())), 1, 86400)) {
            try { $ebook->increment('read_count'); } catch (\Throwable) {}
        }

        return $this->reader($ebook, false);
    }

    public function preview(string $slug): View|RedirectResponse
    {
        $ebook = $this->findOr404($slug);
        if (Shop::canReadFull(auth()->user(), $ebook) && Shop::fullFile($ebook)) {
            return redirect()->route('ebook.read', $ebook->slug ?: $ebook->id);
        }
        if (!Shop::hasSample($ebook)) {
            return redirect()->route('ebook.show', $ebook->slug ?: $ebook->id)->with('info', 'এই ই-বুকের নমুনা অংশ এখনো দেওয়া হয়নি।');
        }

        return $this->reader($ebook, true);
    }

    private function reader(Ebook $ebook, bool $sample): View
    {
        Shop::ensureSchema();
        $user = auth()->user();
        $entry = $sample ? null : Shop::entry($user, $ebook);
        $file = $sample ? Shop::sampleFile($ebook) : Shop::fullFile($ebook);
        $watermark = $sample
            ? 'নমুনা অংশ • আইডিয়া প্রকাশন'
            : ($user ? $user->name . ' • ' . ($user->phone ?: $user->email) : 'আইডিয়া প্রকাশন') . ' • ' . now()->format('d-m-Y');

        return view('ebook::frontend.read', [
            'ebook'        => $ebook,
            'isSample'     => $sample,
            'readerType'   => Shop::kind($file) ?? 'epub',
            'streamUrl'    => route('ebook.stream', $sample ? ['id' => $ebook->id, 'sample' => 1] : ['id' => $ebook->id]),
            'watermarkText'=> $watermark,
            'libraryEntry' => $entry,
            'bookmarks'    => $entry?->bookmarks_data ?? [],
            'highlights'   => $entry?->highlights_data ?? [],
            'startAt'      => $entry?->last_cfi ?: null,
            'startPage'    => (int) ($entry?->last_read_page ?: 1),
            'canSync'      => (bool) $user && !$sample,
            'price'        => Shop::price($ebook),
        ]);
    }

    /** The file for the reader. Full book only with access; the sample otherwise. Never another book's file. */
    public function stream(int|string $id, Request $request): BinaryFileResponse|JsonResponse
    {
        $ebook = Ebook::find($id);
        if (!$ebook || (!$ebook->is_active && !Shop::isStaff(auth()->user(), $ebook))) {
            return response()->json(['message' => 'ই-বুক পাওয়া যায়নি।'], 404);
        }
        $wantFull = $request->query('sample') !== '1' && Shop::canReadFull(auth()->user(), $ebook);
        $path = $wantFull ? Shop::readerCopy(Shop::fullFile($ebook), 'f' . $ebook->id) : Shop::sampleFile($ebook);
        if (!$path) {
            return response()->json(['message' => $wantFull ? 'ই-বুকের ফাইল সার্ভারে পাওয়া যায়নি।' : 'এই ই-বুকের নমুনা অংশ নেই।'], 404);
        }
        $kind = Shop::kind($path);

        $res = response()->file($path, [
            'Content-Type'           => $kind === 'pdf' ? 'application/pdf' : 'application/epub+zip',
            'Content-Disposition'    => 'inline; filename="' . ($ebook->slug ?: 'ebook-' . $ebook->id) . ($kind === 'pdf' ? '.pdf' : '.epub') . '"',
            'Cache-Control'          => 'private, no-store',
            'X-Robots-Tag'           => 'noindex',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        // a bought book is personal: never kept by shared caches
        $res->setPrivate();
        $res->headers->addCacheControlDirective('no-store');

        return $res;
    }

    /** Add a free e-book to the library. */
    public function claim(Request $request, string $slug): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'ফ্রি বইটি লাইব্রেরিতে যুক্ত করতে আগে লগইন করুন।');
        }
        $ebook = $this->findOr404($slug);
        if (!Shop::isFree($ebook)) {
            return redirect()->route('ebook.show', $ebook->slug ?: $ebook->id)->with('error', 'এই বইটি পেইড। সংগ্রহ করতে কিনে নিন।');
        }
        UserEbookLibrary::updateOrCreate(['user_id' => auth()->id(), 'ebook_id' => $ebook->id], ['access_type' => 'free', 'is_active' => true]);

        return redirect()->route('ebook.show', $ebook->slug ?: $ebook->id)->with('success', 'বইটি আপনার লাইব্রেরিতে যুক্ত হয়েছে।');
    }

    /** EPUB download for owners (PDF is read online only). */
    public function download(string $slug): BinaryFileResponse|RedirectResponse
    {
        $ebook = $this->findOr404($slug);
        $user = auth()->user();
        if (!$user) {
            return redirect()->guest(route('login'))->with('info', 'ডাউনলোড করতে আগে লগইন করুন।');
        }
        $back = redirect()->route('ebook.show', $ebook->slug ?: $ebook->id);
        if (!Shop::owns($user, $ebook)) {
            return $back->with('error', Shop::isFree($ebook) ? 'আগে "লাইব্রেরিতে যুক্ত করুন" চাপুন, তারপর ডাউনলোড করুন।' : 'ডাউনলোড করতে আগে বইটি কিনুন।');
        }
        $path = Shop::fullFile($ebook);
        if (!$path || Shop::kind($path) !== 'epub') {
            return $back->with('error', 'এই বইটি শুধু অনলাইনে পড়া যায় (PDF ডাউনলোড বন্ধ)।');
        }
        try { $ebook->increment('download_count'); } catch (\Throwable) {}

        return response()->download($path, ($ebook->slug ?: 'ebook-' . $ebook->id) . '.epub', ['Content-Type' => 'application/epub+zip']);
    }

    /**
     * Reader sync (signed-in readers with access):
     *   action=progress  — position (cfi / page) and percent
     *   action=bookmark  — add a bookmark;  action=unbookmark (id) — remove one
     *   action=highlights — replace the highlight list
     */
    public function saveProgress(Request $request, int|string $id): JsonResponse
    {
        $user = auth()->user();
        $ebook = Ebook::find($id);
        if (!$user || !$ebook) {
            return response()->json(['success' => false], $user ? 404 : 401);
        }
        if (!Shop::canReadFull($user, $ebook)) {
            return response()->json(['success' => false, 'message' => 'অনুমতি নেই'], 403);
        }
        Shop::ensureSchema();
        $v = $request->validate([
            'action'           => 'nullable|in:progress,bookmark,unbookmark,highlights',
            'last_read_page'   => 'nullable|integer|min:1|max:100000',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'cfi'              => 'nullable|string|max:500',
            'bookmark_title'   => 'nullable|string|max:255',
            'bookmark_id'      => 'nullable|string|max:40',
            'highlights'       => 'nullable|array|max:500',
            'highlights.*.cfi' => 'required_with:highlights|string|max:500',
            'highlights.*.text'=> 'nullable|string|max:2000',
            'highlights.*.note'=> 'nullable|string|max:2000',
            'highlights.*.color'=> ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
        ]);
        $entry = UserEbookLibrary::firstOrCreate(
            ['user_id' => $user->id, 'ebook_id' => $ebook->id],
            ['access_type' => Shop::isFree($ebook) ? 'free' : 'purchased', 'is_active' => true]
        );
        $action = $v['action'] ?? 'progress';
        $bookmarks = $entry->bookmarks_data ?? [];

        if ($action === 'bookmark') {
            $bookmarks[] = [
                'id' => uniqid('bm_'), 'page' => $v['last_read_page'] ?? null, 'cfi' => $v['cfi'] ?? null,
                'title' => $v['bookmark_title'] ?? ('পৃষ্ঠা ' . ($v['last_read_page'] ?? '')), 'time' => now()->toIso8601String(),
            ];
            $entry->bookmarks_data = array_slice($bookmarks, -200);
        } elseif ($action === 'unbookmark') {
            $entry->bookmarks_data = array_values(array_filter($bookmarks, fn ($b) => ($b['id'] ?? '') !== ($v['bookmark_id'] ?? '')));
        } elseif ($action === 'highlights') {
            $entry->highlights_data = array_values($v['highlights'] ?? []);
        } else {
            if (!empty($v['cfi'])) $entry->last_cfi = $v['cfi'];
            if (!empty($v['last_read_page'])) $entry->last_read_page = $v['last_read_page'];
            if (isset($v['progress_percent'])) $entry->progress_percent = (int) $v['progress_percent'];
        }
        $entry->save();

        return response()->json(['success' => true, 'bookmarks' => $entry->bookmarks_data ?? []]);
    }

    /** Active book by slug (or id); inactive ones only for the author and staff. */
    private function findOr404(string|int $slug): Ebook
    {
        $raw = rawurldecode(trim((string) $slug));
        $e = Ebook::where('slug', $raw)->first()
            ?: (ctype_digit($raw) ? Ebook::find((int) $raw) : null)
            ?: Ebook::where('slug', \Illuminate\Support\Str::slug($raw))->first();
        if (!$e || (!$e->is_active && !Shop::isStaff(auth()->user(), $e))) {
            abort(404, 'ই-বুকটি পাওয়া যায়নি।');
        }

        return $e;
    }
}
