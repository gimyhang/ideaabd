<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteTranslation;
use App\Support\DbTranslationLoader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * /admin/translations — every row is a lang key. A field left empty uses the
 * lang/*.php text ("Default"); text typed here overrides it on the site.
 */
class TranslationAdminController extends Controller
{
    private const PER_PAGE = 25;
    private const STATUSES = ['all', 'missing', 'custom', 'done'];
    private const GROUP_RULE = 'regex:/^[a-z][a-z0-9_]{0,49}$/';
    private const KEY_RULE = 'regex:/^[a-z0-9_-]+(\.[a-z0-9_-]+)*$/i';

    /** Common store words, used when no saved text matches. */
    private const GLOSSARY = [
        'হোম' => 'Home', 'বই' => 'Books', 'বইসমূহ' => 'Books', 'বইয়ের তালিকা' => 'Book Catalog',
        'লেখক' => 'Author', 'লেখকবৃন্দ' => 'Authors', 'প্রকাশক' => 'Publisher', 'প্রকাশনীসমূহ' => 'Publishers',
        'ই-বুক' => 'E-Book', 'ই-বুক লাইব্রেরি' => 'E-Book Library', 'ডিজিটাল লাইব্রেরি' => 'Digital Library',
        'ই-বুক পড়ুন' => 'Read E-Book', 'কার্ট' => 'Cart', 'কার্টে যোগ করুন' => 'Add to Cart',
        'কার্টে যুক্ত করুন' => 'Add to Cart', 'এখনই কিনুন' => 'Buy Now', 'সরাসরি কিনুন' => 'Buy Now',
        'এখনই অর্ডার করুন' => 'Order Now', 'অর্ডার' => 'Order', 'অর্ডার সম্পন্ন করুন' => 'Place Order',
        'চেকআউট' => 'Checkout', 'মূল্য' => 'Price', 'ছাড়' => 'Discount', 'সাবটোটাল' => 'Subtotal',
        'সর্বমোট' => 'Total', 'ডেলিভারি চার্জ' => 'Delivery Charge', 'ফ্রি ডেলিভারি' => 'Free Delivery',
        'পেমেন্ট মাধ্যম' => 'Payment Method', 'ক্যাশ অন ডেলিভারি' => 'Cash on Delivery',
        'আমার অ্যাকাউন্ট' => 'My Account', 'লগইন' => 'Login', 'লগিন' => 'Login', 'লগআউট' => 'Logout',
        'নিবন্ধন' => 'Sign Up', 'সাইন আপ' => 'Sign Up', 'পাসওয়ার্ড' => 'Password', 'ইমেইল' => 'Email',
        'মোবাইল' => 'Mobile', 'নাম' => 'Name', 'ঠিকানা' => 'Address', 'খুঁজুন' => 'Search', 'অনুসন্ধান' => 'Search',
        'সংরক্ষণ করুন' => 'Save', 'বাতিল' => 'Cancel', 'মুছুন' => 'Delete', 'সম্পাদনা' => 'Edit',
        'আরও দেখুন' => 'View More', 'সব দেখুন' => 'View All', 'যোগাযোগ' => 'Contact', 'আমাদের সম্পর্কে' => 'About Us',
        'ব্লগ' => 'Blog', 'গবেষণা' => 'Research', 'ওয়েবজিন' => 'Webzine', 'আইডিয়া প্রকাশন' => 'Idea Prakashan',
        'সকল অধিকার সংরক্ষিত' => 'All Rights Reserved', 'অর্ডার ট্র্যাকিং' => 'Order Tracking',
        'রয়্যালটি হিসাব' => 'Royalty Statement', 'কাস্টমার সাপোর্ট' => 'Customer Support',
        'ফ্রি নমুনা অংশ' => 'Free Sample', 'উইশলিস্ট' => 'Wishlist', 'পরিমাণ' => 'Quantity', 'স্টকে আছে' => 'In Stock',
        'স্টক শেষ' => 'Out of Stock', 'নতুন' => 'New', 'জনপ্রিয়' => 'Popular', 'বেস্টসেলার' => 'Bestseller',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $group = (string) $request->query('group', '');
        $search = trim((string) $request->query('search', ''));
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : 'all';

        $all = $this->rows();
        $groups = $all->groupBy('group')->map(fn ($g, $name) => [
            'name'    => $name,
            'total'   => $g->count(),
            'done'    => $g->where('done', true)->count(),
            'percent' => $g->count() ? (int) floor($g->where('done', true)->count() * 100 / $g->count()) : 100,
        ])->sortKeys()->values();

        $filtered = $all
            ->when($group !== '', fn ($c) => $c->where('group', $group))
            ->when($search !== '', function ($c) use ($search) {
                $needle = mb_strtolower($search);
                return $c->filter(fn ($r) => str_contains(mb_strtolower($r['group'] . '.' . $r['key'] . ' ' . implode(' ', array_column($r['langs'], 'value'))), $needle));
            })
            ->when($status === 'missing', fn ($c) => $c->where('done', false))
            ->when($status === 'custom', fn ($c) => $c->where('custom', true))
            ->when($status === 'done', fn ($c) => $c->where('done', true))
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, self::PER_PAGE)->values(),
            $filtered->count(), self::PER_PAGE, $page,
            ['path' => route('admin.translations.index'), 'query' => array_filter(['group' => $group, 'search' => $search, 'status' => $status === 'all' ? null : $status])]
        );

        $stats = $this->stats($all);
        $canEdit = (bool) $request->user()?->hasPermission('settings.manage');
        $data = compact('paginator', 'groups', 'group', 'search', 'status', 'stats', 'canEdit');

        if ($request->boolean('partial')) {
            return response()->json([
                'rows'   => view('admin.translations._rows', $data)->render(),
                'stats'  => $stats,
                'groups' => $groups,
                'count'  => $filtered->count(),
            ]);
        }

        $data['fileGroups'] = $this->fileGroups();
        return view('admin.translations.index', $data);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $v = $request->validate([
            'group'   => ['required', 'string', 'max:50', self::GROUP_RULE],
            'key'     => ['required', 'string', 'max:100', self::KEY_RULE],
            'text_bn' => 'nullable|string|max:2000',
            'text_en' => 'nullable|string|max:2000',
            'text_ar' => 'nullable|string|max:2000',
        ], [
            'group.regex' => 'Group: small English letters, numbers and _ only.',
            'key.regex'   => 'Key: English letters, numbers, _ - and dots only.',
        ]);

        if (SiteTranslation::where('group', $v['group'])->where('key', $v['key'])->exists()) {
            throw ValidationException::withMessages(['key' => "“{$v['group']}.{$v['key']}” already exists — edit it in the list."]);
        }
        $this->assertFits($v['group'], $v['key']);

        $row = SiteTranslation::create([
            'group'   => $v['group'],
            'key'     => $v['key'],
            'text_bn' => $this->clean($v['text_bn'] ?? null),
            'text_en' => $this->clean($v['text_en'] ?? null),
            'text_ar' => $this->clean($v['text_ar'] ?? null),
        ]);
        DbTranslationLoader::forget($row->group);

        $message = "Saved {$row->group}.{$row->key}";
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'id' => $row->id])
            : redirect()->route('admin.translations.index')->with('success', $message);
    }

    public function update(Request $request, SiteTranslation $translation): JsonResponse|RedirectResponse
    {
        $v = $request->validate([
            'text_bn' => 'nullable|string|max:2000',
            'text_en' => 'nullable|string|max:2000',
            'text_ar' => 'nullable|string|max:2000',
        ]);

        // only the fields that were sent change (inline edit sends one)
        $changes = [];
        foreach (array_keys(DbTranslationLoader::LOCALES) as $loc) {
            if ($request->exists('text_' . $loc)) {
                $changes['text_' . $loc] = $this->clean($v['text_' . $loc] ?? null);
            }
        }
        $translation->update($changes);
        DbTranslationLoader::forget($translation->group);

        if ($request->expectsJson()) {
            $row = $this->rows(collect([$translation->fresh()]))->first();
            return response()->json(['message' => 'Saved', 'row' => $row, 'stats' => $this->stats($this->rows())]);
        }
        return redirect()->back()->with('success', 'Saved');
    }

    public function destroy(Request $request, SiteTranslation $translation): JsonResponse
    {
        $group = $translation->group;
        $translation->delete();
        DbTranslationLoader::forget($group);

        return response()->json(['message' => 'Deleted — the file text is used again', 'stats' => $this->stats($this->rows())]);
    }

    /** Lists every lang/*.php key so it can be edited here. Typed text is never touched. */
    public function sync(): JsonResponse
    {
        $loader = $this->loader();
        $existing = SiteTranslation::get(['group', 'key'])->map(fn ($r) => $r->group . '|' . $r->key)->flip();
        $added = 0;

        foreach ($this->fileGroups() as $group) {
            $keys = [];
            foreach (array_keys(DbTranslationLoader::LOCALES) as $loc) {
                $keys += Arr::dot($loader->fileLines($loc, $group));
            }
            foreach (array_keys($keys) as $key) {
                if (!is_string($keys[$key]) || strlen($key) > 100 || isset($existing[$group . '|' . $key])) {
                    continue;
                }
                // left empty on purpose: empty = keep using the file text
                SiteTranslation::create(['group' => $group, 'key' => $key]);
                $existing[$group . '|' . $key] = true;
                $added++;
            }
            DbTranslationLoader::forget($group);
        }

        return response()->json([
            'message' => $added ? "{$added} keys added from the language files" : 'Already up to date',
            'added'   => $added,
        ]);
    }

    /** Fills empty English text from saved translations and the store glossary. */
    public function fill(): JsonResponse
    {
        $filled = 0;
        $rows = $this->rows();
        foreach ($rows as $row) {
            if ($row['langs']['en']['value'] !== '' || $row['langs']['bn']['value'] === '') {
                continue;
            }
            $hit = $this->suggestFor($row['langs']['bn']['value'], $row['id'], $rows);
            if ($hit) {
                SiteTranslation::whereKey($row['id'])->update(['text_en' => $hit['text']]);
                DbTranslationLoader::forget($row['group']);
                $filled++;
            }
        }

        return response()->json([
            'message' => $filled ? "{$filled} English texts filled" : 'No matches found — the rest need to be typed',
            'filled'  => $filled,
            'stats'   => $this->stats($this->rows()),
        ]);
    }

    /** English suggestion for a Bangla text (route name: auto-translate). */
    public function autoTranslate(Request $request): JsonResponse
    {
        $v = $request->validate(['text_bn' => 'required|string|max:2000', 'id' => 'nullable|integer']);
        $hit = $this->suggestFor($v['text_bn'], (int) ($v['id'] ?? 0));

        if (!$hit) {
            return response()->json(['success' => false, 'message' => 'No suggestion — type the English text'], 404);
        }
        return response()->json(['success' => true, 'text_en' => $hit['text'], 'source' => $hit['source']]);
    }

    public function export(): StreamedResponse
    {
        $rows = $this->rows();
        $safe = fn (string $s) => preg_match('/^[=+\-@\t\r]/', $s) ? "'" . $s : $s;

        return response()->streamDownload(function () use ($rows, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['group', 'key', 'bangla', 'english', 'arabic', 'custom']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['group'], $r['key'], $safe($r['langs']['bn']['value']), $safe($r['langs']['en']['value']), $safe($r['langs']['ar']['value']), $r['custom'] ? 'yes' : 'no']);
            }
            fclose($out);
        }, 'translations-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    /**
     * Rows with the text the site really shows: saved text, else the file text.
     */
    private function rows(?Collection $models = null): Collection
    {
        $models ??= SiteTranslation::orderBy('group')->orderBy('key')->get();
        $loader = $this->loader();
        $files = [];

        return $models->map(function (SiteTranslation $t) use ($loader, &$files) {
            $langs = [];
            foreach (DbTranslationLoader::LOCALES as $loc => $col) {
                $files[$loc][$t->group] ??= $loader->fileLines($loc, $t->group);
                $default = Arr::get($files[$loc][$t->group], $t->key);
                $default = is_string($default) ? $default : '';
                $saved = trim((string) $t->{$col});
                $langs[$loc] = ['saved' => $saved, 'default' => $default, 'value' => $saved !== '' ? $saved : $default];
            }
            $vars = $this->placeholders($langs['bn']['value']);

            return [
                'id'      => $t->id,
                'group'   => $t->group,
                'key'     => $t->key,
                'langs'   => $langs,
                'custom'  => $langs['bn']['saved'] !== '' || $langs['en']['saved'] !== '' || $langs['ar']['saved'] !== '',
                'done'    => $langs['bn']['value'] !== '' && $langs['en']['value'] !== '',
                'in_file' => $langs['bn']['default'] !== '' || $langs['en']['default'] !== '',
                'vars'    => $vars,
                'warn'    => $langs['en']['value'] !== '' && array_diff($vars, $this->placeholders($langs['en']['value'])) ? true : false,
                'updated' => optional($t->updated_at)->diffForHumans(),
            ];
        });
    }

    private function stats(Collection $rows): array
    {
        $total = $rows->count();
        $count = fn ($loc) => $rows->filter(fn ($r) => $r['langs'][$loc]['value'] !== '')->count();
        $pct = fn ($n) => $total ? (int) floor($n * 100 / $total) : 100;
        [$bn, $en, $ar] = [$count('bn'), $count('en'), $count('ar')];

        return [
            'total'   => $total,
            'groups'  => $rows->pluck('group')->unique()->count(),
            'custom'  => $rows->where('custom', true)->count(),
            'missing' => $rows->where('done', false)->count(),
            'warn'    => $rows->where('warn', true)->count(),
            'langs'   => [
                'bn' => ['count' => $bn, 'percent' => $pct($bn)],
                'en' => ['count' => $en, 'percent' => $pct($en)],
                'ar' => ['count' => $ar, 'percent' => $pct($ar)],
            ],
        ];
    }

    private function suggestFor(string $bn, int $exceptId = 0, ?Collection $rows = null): ?array
    {
        $bn = trim($bn);
        if ($bn === '') {
            return null;
        }
        foreach ($rows ?? $this->rows() as $r) {
            if ($r['id'] !== $exceptId && $r['langs']['bn']['value'] === $bn && $r['langs']['en']['value'] !== '') {
                return ['text' => $r['langs']['en']['value'], 'source' => 'memory'];
            }
        }
        $plain = preg_replace('/[।!?.\s]+$/u', '', $bn) ?? $bn;
        if (isset(self::GLOSSARY[$plain])) {
            return ['text' => self::GLOSSARY[$plain], 'source' => 'glossary'];
        }
        return null;
    }

    private function placeholders(string $text): array
    {
        preg_match_all('/:([a-z_]+)/i', $text, $m);
        return array_values(array_unique($m[1]));
    }

    private function assertFits(string $group, string $key): void
    {
        $loader = $this->loader();
        foreach (array_keys(DbTranslationLoader::LOCALES) as $loc) {
            if (!DbTranslationLoader::fits($loader->fileLines($loc, $group), $key)) {
                throw ValidationException::withMessages(['key' => "“{$group}.{$key}” is a set of lines in the language file — add a key inside it instead."]);
            }
        }
    }

    /** Groups found in lang/bn and in Laravel's own English files. */
    private function fileGroups(): array
    {
        $dirs = [lang_path('bn'), lang_path('en'), base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en')];
        $groups = [];
        foreach ($dirs as $dir) {
            if (File::isDirectory($dir)) {
                foreach (File::files($dir) as $f) {
                    if ($f->getExtension() === 'php') {
                        $groups[] = $f->getFilenameWithoutExtension();
                    }
                }
            }
        }
        $groups = array_values(array_unique($groups));
        sort($groups);
        return $groups;
    }

    private function loader(): DbTranslationLoader
    {
        $loader = app('translator')->getLoader();
        return $loader instanceof DbTranslationLoader ? $loader : new DbTranslationLoader($loader);
    }

    private function clean(?string $text): ?string
    {
        $text = trim((string) $text);
        return $text === '' ? null : $text;
    }
}
