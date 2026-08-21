<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\Moderatable;
use App\Models\User;
use App\Support\ContentTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * One controller for every admin-managed content type.
 *
 * Two things the platform needs that a plain resource controller does not give
 * us, and which drive most of the code here:
 *
 *  1. Moderation — an admin approves, rejects or deletes anything a seller,
 *     publisher or author submitted.
 *  2. Posting on behalf of someone else — contributors who cannot register
 *     online still get their books, author pages and articles published, with
 *     the credit recorded against their name.
 *
 * The shape of each type (fields, validation, upload folders) lives in
 * {@see ContentTypes}; nothing type-specific is hard-coded below.
 */
class ContentController extends Controller
{
    /** Where an uploaded file lands, relative to storage/app/public. */
    private const UPLOAD_DISK = 'public';

    /**
     * Everything waiting for a decision, across all six content types.
     *
     * Types whose table or moderation columns are missing are skipped rather
     * than fataling, so the page works on a partially migrated deployment.
     */
    public function queue(Request $request): View
    {
        $status  = $request->string('status')->toString() ?: 'pending';
        $status  = in_array($status, ['pending', 'approved', 'rejected', 'all'], true) ? $status : 'pending';
        $pending = [];
        $items   = [];

        foreach (ContentTypes::all() as $key => $spec) {
            if (! Schema::hasTable($spec['table']) || ! Schema::hasColumn($spec['table'], 'mod_status')) {
                continue;
            }

            $rows = DB::table($spec['table'])
                ->when($status !== 'all', fn ($q) => $q->where('mod_status', $status))
                ->when(
                    Schema::hasColumn($spec['table'], 'deleted_at'),
                    fn ($q) => $q->whereNull('deleted_at')
                )
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            $pending[$key] = DB::table($spec['table'])->where('mod_status', 'pending')->count();

            foreach ($rows as $row) {
                $items[] = [
                    'type'    => $key,
                    'label'   => $spec['label'],
                    'icon'    => $spec['icon'],
                    'id'      => $row->id,
                    'title'   => $row->{$spec['display']} ?? '—',
                    'status'  => $row->mod_status ?? 'approved',
                    'credit'  => $row->owner_name ?: null,
                    'reason'  => $row->rejection_reason ?? null,
                    'created' => $row->created_at ?? null,
                ];
            }
        }

        usort($items, fn ($a, $b) => strcmp((string) $b['created'], (string) $a['created']));

        return view('admin.content.queue', [
            'items'   => $items,
            'pending' => $pending,
            'status'  => $status,
        ]);
    }

    public function create(string $type): View
    {
        $spec = ContentTypes::get($type);

        $this->guardTableExists($spec);

        return view('admin.content.form', [
            'spec'      => $spec,
            'record'    => null,
            'lookups'   => $this->lookups($spec, null),
            'creditees' => $this->creditees(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $spec = ContentTypes::get($type);

        $this->guardTableExists($spec);

        $data = $this->validated($request, $spec, null);

        /** @var Model $record */
        $record = new $spec['model']();
        $attributes = $this->attributesFrom($request, $spec, $data, $record);

        $attributes['slug'] = $this->uniqueSlug(
            $spec,
            $data[$spec['slugFrom']] ?? $spec['label'],
            null
        );

        $credit = $this->creditAttributes($request, $spec, isNew: true, record: null);
        foreach ($credit as $k => $v) {
            $attributes[$k] = $v;
        }

        if ($type === 'blog') {
            if (empty($attributes['author_id']) || !is_numeric($attributes['author_id'])) {
                $attributes['author_id'] = (int) (auth()->id() ?: 1);
            } else {
                $attributes['author_id'] = (int) $attributes['author_id'];
            }
            if (($attributes['status'] ?? null) === 'published' && empty($attributes['published_at'])) {
                $attributes['published_at'] = now();
            }
        }

        try {
            $record->forceFill($attributes)->save();
            if ($type === 'books') {
                $authorIds = array_filter((array) $request->input('author_ids', []));
                if (!empty($record->author_link_id) && !in_array($record->author_link_id, $authorIds)) {
                    $authorIds[] = (int) $record->author_link_id;
                }
                if (!empty($authorIds)) {
                    $record->authors()->sync($authorIds);
                }
            }
            if ($type === 'webzines') {
                $this->syncWebzineArticles($record, $request);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint on blog_posts.title for SQLite or MySQL
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed: blog_posts.title') || str_contains($e->getMessage(), 'blog_posts_title_unique') || str_contains($e->getMessage(), 'Duplicate entry')) {
                try {
                    $attributes['title'] = $attributes['title'] . ' (' . Str::random(4) . ')';
                    $record->forceFill($attributes)->save();
                } catch (\Throwable $e2) {
                    \Illuminate\Support\Facades\Log::error("ContentController store retry error ({$type}): " . $e2->getMessage());
                    return back()->withInput()->withErrors([
                        'error' => "সংরক্ষণ করার সময় ত্রুটি ঘটেছে: " . $e2->getMessage(),
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::error("ContentController store error ({$type}): " . $e->getMessage());
                return back()->withInput()->withErrors([
                    'error' => "সংরক্ষণ করার সময় ত্রুটি ঘটেছে: " . $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("ContentController store error ({$type}): " . $e->getMessage());
            return back()->withInput()->withErrors([
                'error' => "সংরক্ষণ করার সময় ত্রুটি ঘটেছে: " . $e->getMessage(),
            ]);
        }

        return redirect()
            ->route($spec['listRoute'])
            ->with('success', "{$spec['label']} যোগ করা হয়েছে — “{$record->{$spec['display']}}”।");
    }

    public function show(string $type, int $id): RedirectResponse
    {
        return redirect()->route('admin.content.edit', ['type' => $type, 'id' => $id]);
    }

    public function edit(string $type, int $id): View|RedirectResponse
    {
        $spec = ContentTypes::get($type);
        try {
            $record = $this->findRecord($spec, $id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route($spec['listRoute'])
                ->with('error', "অনুরোধকৃত {$spec['label']}টি (ID: #{$id}) পাওয়া যায়নি বা ডাটাবেজ থেকে অপসারিত হয়েছে।");
        }

        return view('admin.content.form', [
            'spec'      => $spec,
            'record'    => $record,
            'lookups'   => $this->lookups($spec, $record),
            'creditees' => $this->creditees(),
        ]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $spec = ContentTypes::get($type);
        try {
            $record = $this->findRecord($spec, $id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route($spec['listRoute'])
                ->with('error', "অনুরোধকৃত {$spec['label']}টি (ID: #{$id}) পাওয়া যায়নি।");
        }

        $data       = $this->validated($request, $spec, $record);
        $attributes = $this->attributesFrom($request, $spec, $data, $record);

        // Re-slug only when the admin cleared the field or renamed the entry and
        // the old slug was derived from the old name.
        if ($request->filled('slug')) {
            $attributes['slug'] = $this->uniqueSlug($spec, (string) $request->input('slug'), $record);
        }

        $credit = $this->creditAttributes($request, $spec, isNew: false, record: $record);
        foreach ($credit as $k => $v) {
            $attributes[$k] = $v;
        }

        if ($type === 'blog') {
            if (empty($attributes['author_id']) || !is_numeric($attributes['author_id'])) {
                $attributes['author_id'] = (int) ($record->author_id ?: (auth()->id() ?: 1));
            } else {
                $attributes['author_id'] = (int) $attributes['author_id'];
            }
            if (($attributes['status'] ?? null) === 'published' && empty($record->published_at)) {
                $attributes['published_at'] = now();
            }
        }

        try {
            $record->forceFill($attributes)->save();
            if ($type === 'books') {
                $authorIds = array_filter((array) $request->input('author_ids', []));
                if (!empty($record->author_link_id) && !in_array($record->author_link_id, $authorIds)) {
                    $authorIds[] = (int) $record->author_link_id;
                }
                if (!empty($authorIds)) {
                    $record->authors()->sync($authorIds);
                }
            }
            if ($type === 'webzines') {
                $this->syncWebzineArticles($record, $request);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint on blog_posts.title for SQLite or MySQL
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed: blog_posts.title') || str_contains($e->getMessage(), 'blog_posts_title_unique') || str_contains($e->getMessage(), 'Duplicate entry')) {
                try {
                    $attributes['title'] = $attributes['title'] . ' (' . Str::random(4) . ')';
                    $record->forceFill($attributes)->save();
                } catch (\Throwable $e2) {
                    \Illuminate\Support\Facades\Log::error("ContentController update error on retry ({$type} #{$id}): " . $e2->getMessage());
                    return back()->withInput()->withErrors([
                        'error' => "হালনাগাদ করার সময় ত্রুটি ঘটেছে: " . $e2->getMessage(),
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::error("ContentController update error ({$type} #{$id}): " . $e->getMessage());
                return back()->withInput()->withErrors([
                    'error' => "হালনাগাদ করার সময় ত্রুটি ঘটেছে: " . $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("ContentController update error ({$type} #{$id}): " . $e->getMessage());
            return back()->withInput()->withErrors([
                'error' => "হালনাগাদ করার সময় ত্রুটি ঘটেছে: " . $e->getMessage(),
            ]);
        }

        return redirect()
            ->route($spec['listRoute'])
            ->with('success', "{$spec['label']} সফলভাবে হালনাগাদ করা হয়েছে।");
    }

    // ─── Moderation ─────────────────────────────────────────────────────

    public function approve(string $type, int $id): RedirectResponse
    {
        $spec   = ContentTypes::get($type);
        $record = $this->findRecord($spec, $id);

        $this->guardModeratable($record);
        $record->markApproved(auth()->id());

        // Approving is also what puts the entry live on the site.
        $this->setVisibility($record, true);

        // If it's a blog post, dispatch approval email to author
        if ($type === 'blog' && $record instanceof \Modules\Blog\Models\BlogPost) {
            if ($record->author_id) {
                $author = \App\Models\User::find($record->author_id);
                if ($author && $author->email && !str_ends_with($author->email, '@buyer.ideaabd.com')) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($author->email)->send(new \App\Mail\BlogPostApprovedMail($record, $author));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Could not send blog approval email from ContentController: " . $e->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "{$spec['label']} অনুমোদন করা হয়েছে এবং সংশ্লিষ্ট লেখককে ইমেইল পাঠানো হয়েছে।");
    }

    public function reject(Request $request, string $type, int $id): RedirectResponse
    {
        $spec   = ContentTypes::get($type);
        $record = $this->findRecord($spec, $id);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ], [], ['reason' => 'কারণ']);

        $this->guardModeratable($record);
        $record->markRejected($validated['reason'] ?? null, auth()->id());

        $this->setVisibility($record, false);

        return back()->with('success', "{$spec['label']} বাতিল করা হয়েছে।");
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $spec   = ContentTypes::get($type);
        $record = $this->findRecord($spec, $id);

        $label = $record->{$spec['display']};
        $record->delete();

        return redirect()
            ->route($spec['listRoute'])
            ->with('success', "“{$label}” মুছে ফেলা হয়েছে।");
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        $spec = ContentTypes::get($type);

        abort_unless($this->softDeletes($spec['model']), 404);

        $record = $spec['model']::onlyTrashed()->findOrFail($id);
        $record->restore();

        return back()->with('success', "{$spec['label']} ফিরিয়ে আনা হয়েছে।");
    }

    // ─── internals ──────────────────────────────────────────────────────

    /**
     * Build the validation rules from the field spec.
     *
     * FK existence rules are appended only when the lookup table has actually
     * been migrated — otherwise `exists:` would throw on a half-migrated
     * deployment instead of showing a validation error.
     *
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function validated(Request $request, array $spec, ?Model $record): array
    {
        $rules      = [];
        $attributes = [];

        foreach ($spec['fields'] as $name => $field) {
            // author_role_group is a virtual composite field — its real
            // columns (author_role, author_name, author_link_id) are validated separately.
            if ($field['type'] === 'author_role_group') {
                $rules['author_role']       = ['nullable', 'string', 'in:author,translator,editor'];
                $rules['author_name']       = ['nullable', 'string', 'max:255'];
                $rules['author_input_mode'] = ['nullable', 'string', 'in:directory,custom'];
                $rules['author_link_id']    = ['nullable', 'integer'];
                if (Schema::hasTable('authors')) {
                    $rules['author_link_id'][] = Rule::exists('authors', 'id');
                }
                $attributes['author_role']    = 'ভূমিকা';
                $attributes['author_name']    = 'লেখকের নাম';
                $attributes['author_link_id'] = 'লেখক (ডিরেক্টরি)';
                continue;
            }

            $fieldRules = explode('|', $field['rules']);

            if (! empty($field['lookup']) && Schema::hasTable($field['lookup'])) {
                $fieldRules[] = Rule::exists($field['lookup'], 'id');
            }

            if (! empty($field['unique'])) {
                $unique = Rule::unique($spec['table'], $name);
                if ($this->softDeletes($spec['model'])) {
                    $unique->withoutTrashed();
                }
                $fieldRules[] = $record ? $unique->ignore($record->getKey()) : $unique;
            }

            $rules[$name]      = $fieldRules;
            $attributes[$name] = $field['label'];
        }

        $rules['slug']         = ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\-_]+$/u'];
        $rules['owner_name']   = ['nullable', 'string', 'max:255'];
        $rules['owner_phone']  = ['nullable', 'string', 'max:30'];
        $rules['submitted_by'] = ['nullable', 'integer', Rule::exists('users', 'id')];
        $rules['mod_status']   = ['nullable', Rule::in([
            'pending', 'approved', 'rejected',
        ])];

        if (($spec['key'] ?? null) === 'books') {
            $rules['category_id'] = ['required', 'integer', 'exists:categories,id'];
            $rules['price'] = ['nullable', 'numeric', 'min:0', 'max:9999999'];
            $rules['hardcover_price'] = ['nullable', 'numeric', 'min:0', 'max:9999999'];
            $rules['cost_price'] = ['nullable', 'numeric', 'min:0', 'max:9999999'];
            $rules['discount_price'] = ['nullable', 'numeric', 'min:0', 'max:9999999'];
            
            // Require at least one price (List Price / MRP)
            if (!$request->filled('price') && !$request->filled('hardcover_price')) {
                $rules['price'] = ['required', 'numeric', 'min:0', 'max:9999999'];
            }
            
            $attributes['category_id'] = 'মূল ক্যাটাগরি';
            $attributes['hardcover_price'] = 'হার্ডকভার নিয়মিত মূল্য';
            $attributes['price'] = 'নিয়মিত মূল্য (List Price)';
        }

        $attributes += [
            'slug'         => 'slug',
            'owner_name'   => 'যার পক্ষে',
            'owner_phone'  => 'ফোন',
            'submitted_by' => 'ব্যবহারকারী',
            'mod_status'   => 'অনুমোদন অবস্থা',
        ];

        $validated = $request->validate($rules, [], $attributes);

        if ($request->filled('summary')) {
            $summaryClean = trim(strip_tags((string) $request->input('summary')));
            $words = preg_split('/\s+/u', $summaryClean, -1, PREG_SPLIT_NO_EMPTY);
            $limit = (($spec['key'] ?? null) === 'books') ? 1000 : 400;
            if (count($words) > $limit) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'summary' => "বইয়ের সারসংক্ষেপ (Product Summary) সর্বোচ্চ {$limit} শব্দের মধ্যে হতে হবে। বর্তমানে " . count($words) . ' টি শব্দ রয়েছে।',
                ]);
            }
        }

        if ($request->filled('description')) {
            $descClean = trim(strip_tags((string) $request->input('description')));
            $words = preg_split('/\s+/u', $descClean, -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 400) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'description' => 'বিস্তারিত ফ্ল্যাপ ও বিবরণ সর্বোচ্চ ৪০০ শব্দের মধ্যে হতে হবে। বর্তমানে ' . count($words) . ' টি শব্দ রয়েছে।',
                ]);
            }
        }

        if ($request->filled('author_bio')) {
            $bioClean = trim(strip_tags((string) $request->input('author_bio')));
            $words = preg_split('/\s+/u', $bioClean, -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 300) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'author_bio' => 'লেখক পরিচিতি সর্বোচ্চ ৩০০ শব্দের মধ্যে হতে হবে। বর্তমানে ' . count($words) . ' টি শব্দ রয়েছে।',
                ]);
            }
        }

        return $validated;
    }

    /**
     * Turn validated input into a column => value map.
     *
     * Only keys named in the field spec ever reach the model, so `forceFill`
     * below cannot be used to set a column the admin form does not expose.
     *
     * @param  array<string, mixed>  $spec
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFrom(Request $request, array $spec, array $data, Model $record): array
    {
        $attributes = [];

        foreach ($spec['fields'] as $name => $field) {
            $type = $field['type'];

            // Virtual composite field — expand into the real DB columns.
            // In 'directory' mode we link to the authors table; in 'custom' mode
            // we store a free-text name. Either way author_role is always saved.
            if ($type === 'author_role_group') {
                $mode = $request->input('author_input_mode', 'custom');

                if (Schema::hasColumn($spec['table'], 'author_role')) {
                    $attributes['author_role'] = $request->input('author_role') ?: null;
                }

                if ($mode === 'directory') {
                    $linkId = $request->integer('author_link_id') ?: null;
                    if (Schema::hasColumn($spec['table'], 'author_link_id')) {
                        $attributes['author_link_id'] = $linkId;
                    }
                    // Resolve the name from the authors table so both columns stay in sync
                    if (Schema::hasColumn($spec['table'], 'author_name') && $linkId) {
                        $attributes['author_name'] = DB::table('authors')->where('id', $linkId)->value('name') ?: null;
                    } elseif (Schema::hasColumn($spec['table'], 'author_name')) {
                        $attributes['author_name'] = null;
                    }
                } else {
                    // Custom mode or text input: Unified Author lookup / creation
                    $authorName = trim((string) $request->input('author_name'));
                    if ($authorName !== '') {
                        $author = \Modules\Author\Models\Author::findOrCreateUnified([
                            'name'      => $authorName,
                            'is_active' => true,
                        ]);
                        
                        if (Schema::hasColumn($spec['table'], 'author_link_id')) {
                            $attributes['author_link_id'] = $author->id;
                        }
                        if (Schema::hasColumn($spec['table'], 'author_name')) {
                            $attributes['author_name'] = $author->name;
                        }
                    } else {
                        if (Schema::hasColumn($spec['table'], 'author_link_id')) {
                            $attributes['author_link_id'] = null;
                        }
                        if (Schema::hasColumn($spec['table'], 'author_name')) {
                            $attributes['author_name'] = null;
                        }
                    }
                }
                continue;
            }

            if ($type === 'file') {
                $stored = $this->handleUpload($request, $name, $field, $record);

                if ($stored !== false) {
                    $attributes[$name] = $stored;
                }

                continue;
            }

            if ($type === 'checkbox') {
                $attributes[$name] = $request->boolean($name);

                continue;
            }

            $value = $data[$name] ?? null;

            if ($name === 'category_id' && $request->filled('sub_category_name')) {
                $mainCategoryId = $data['category_id'] ?? null;
                $subCatName = trim((string) $request->input('sub_category_name'));
                
                if ($subCatName !== '') {
                    $existingSubCat = DB::table('categories')
                        ->where('name', $subCatName)
                        ->when($mainCategoryId, fn($q) => $q->where('parent_id', $mainCategoryId))
                        ->first();
                        
                    if ($existingSubCat) {
                        $attributes['category_id'] = $existingSubCat->id;
                    } else {
                        $baseSlug = Str::slug($this->bengaliToEnglish($subCatName)) ?: 'cat-' . Str::random(6);
                        $subCatSlug = $baseSlug;
                        $counter = 1;
                        while (DB::table('categories')->where('slug', $subCatSlug)->exists()) {
                            $subCatSlug = $baseSlug . '-' . $counter++;
                        }

                        $attributes['category_id'] = DB::table('categories')->insertGetId([
                            'parent_id'  => $mainCategoryId ?: null,
                            'name'       => $subCatName,
                            'slug'       => $subCatSlug,
                            'is_active'  => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }
            }

            if ($name === 'category_id' && $spec['key'] === 'blog' && $request->filled('new_blog_category_name')) {
                $blogCatName = trim((string) $request->input('new_blog_category_name'));
                if ($blogCatName !== '') {
                    $existingCat = DB::table('blog_categories')
                        ->where('name', $blogCatName)
                        ->first();
                    if ($existingCat) {
                        $attributes['category_id'] = $existingCat->id;
                    } else {
                        $baseSlug = Str::slug($this->bengaliToEnglish($blogCatName)) ?: 'blog-cat-' . Str::random(6);
                        $catSlug = $baseSlug;
                        $counter = 1;
                        while (DB::table('blog_categories')->where('slug', $catSlug)->exists()) {
                            $catSlug = $baseSlug . '-' . $counter++;
                        }

                        $attributes['category_id'] = DB::table('blog_categories')->insertGetId([
                            'name'        => $blogCatName,
                            'slug'        => $catSlug,
                            'icon'        => 'feather-pointed',
                            'is_active'   => true,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                    continue;
                }
            }

            if ($name === 'publisher_id' && $request->filled('new_publisher_name')) {
                $pubName = trim((string) $request->input('new_publisher_name'));
                if ($pubName !== '') {
                    $existingPub = DB::table('publishers')
                        ->where('name', $pubName)
                        ->first();
                        
                    if ($existingPub) {
                        $attributes['publisher_id'] = $existingPub->id;
                    } else {
                        $baseSlug = Str::slug($this->bengaliToEnglish($pubName)) ?: 'pub-' . Str::random(6);
                        $pubSlug = $baseSlug;
                        $counter = 1;
                        while (DB::table('publishers')->where('slug', $pubSlug)->exists()) {
                            $pubSlug = $baseSlug . '-' . $counter++;
                        }

                        $attributes['publisher_id'] = DB::table('publishers')->insertGetId([
                            'name'       => $pubName,
                            'slug'       => $pubSlug,
                            'is_active'  => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }
            }

            if ($type === 'editor' || $type === 'textarea') {
                $value = $value === null ? null : strip_tags((string) $value, '<p><br><b><strong><i><em><u><s><ul><ol><li><a><h2><h3><h4><h5><h6><blockquote><pre><code><div><span><hr><img>');
            }

            if ($spec['key'] === 'blog' && $name === 'excerpt' && empty($value) && !empty($request->input('content'))) {
                $value = Str::limit(strip_tags((string) $request->input('content')), 200);
            }

            if (($value === '' || $value === null) && isset($field['default'])) {
                $value = $field['default'];
            }

            $attributes[$name] = $value === '' ? null : $value;
        }

        if ($spec['table'] === 'ebooks') {
            if ($request->hasFile('file_path')) {
                $uploaded = $request->file('file_path');
                $ext = strtolower($uploaded->getClientOriginalExtension());
                $attributes['file_type'] = $ext;
                $sizeBytes = $uploaded->getSize();
                $attributes['file_size'] = $sizeBytes >= 1048576 
                    ? round($sizeBytes / 1048576, 1) . ' MB' 
                    : round($sizeBytes / 1024) . ' KB';
            }
            $hasEpub = $request->hasFile('epub_file_path') || !empty($attributes['epub_file_path']) || ($attributes['file_type'] ?? '') === 'epub';
            $hasPdf = !empty($attributes['file_path']) && ($attributes['file_type'] ?? '') === 'pdf';
            if ($hasEpub && $hasPdf) {
                $attributes['format'] = 'both';
            } elseif ($hasEpub) {
                $attributes['format'] = 'epub';
            } else {
                $attributes['format'] = 'pdf';
            }
        }

        if ($spec['key'] === 'books') {
            if ($request->filled('title_en')) {
                $attributes['title_en'] = $request->input('title_en');
            }

            // Sync price and hardcover_price
            $p = $request->input('price');
            $hp = $request->input('hardcover_price');
            if ($p !== null && $p !== '' && ($hp === null || $hp === '')) {
                $attributes['hardcover_price'] = (float)$p;
            } elseif ($hp !== null && $hp !== '' && ($p === null || $p === '')) {
                $attributes['price'] = (float)$hp;
            }

            // Auto calculate discount_price if sold_percent is given
            if ($request->filled('sold_percent') && ($p || $hp)) {
                $basePrice = (float)($p ?: $hp);
                $soldPct = (float)$request->input('sold_percent');
                if ($soldPct > 0 && $soldPct <= 100) {
                    $attributes['discount_price'] = round($basePrice * (1 - $soldPct / 100), 2);
                }
            }

            // Handle Multiple Authors
            if ($request->has('author_names')) {
                $authorNames = array_filter(array_map('trim', (array) $request->input('author_names')));
                if (!empty($authorNames)) {
                    $attributes['author_name'] = implode(', ', $authorNames);
                }
            }

            // Handle Multiple Translators
            if ($request->has('translator_names')) {
                $translators = array_filter(array_map('trim', (array) $request->input('translator_names')));
                if (!empty($translators)) {
                    $attributes['translator_name'] = implode(', ', $translators);
                } elseif (!$request->filled('translator_name')) {
                    $attributes['translator_name'] = null;
                }
            } elseif ($request->filled('translator_name')) {
                $attributes['translator_name'] = $request->input('translator_name');
            }

            // Handle Multiple Editors
            if ($request->has('editor_names')) {
                $editors = array_filter(array_map('trim', (array) $request->input('editor_names')));
                if (!empty($editors)) {
                    $attributes['editor_name'] = implode(', ', $editors);
                } elseif (!$request->filled('editor_name')) {
                    $attributes['editor_name'] = null;
                }
            } elseif ($request->filled('editor_name')) {
                $attributes['editor_name'] = $request->input('editor_name');
            }

            // Handle Multiple Rewriters
            if ($request->has('rewriter_names')) {
                $rewriters = array_filter(array_map('trim', (array) $request->input('rewriter_names')));
                if (!empty($rewriters)) {
                    $attributes['rewriter_name'] = implode(', ', $rewriters);
                } elseif (!$request->filled('rewriter_name')) {
                    $attributes['rewriter_name'] = null;
                }
            } elseif ($request->filled('rewriter_name')) {
                $attributes['rewriter_name'] = $request->input('rewriter_name');
            }

            // Handle Height and Width (cm)
            if ($request->filled('book_height_cm') || $request->filled('book_width_cm')) {
                $h = $request->input('book_height_cm');
                $w = $request->input('book_width_cm');
                $attributes['book_height_cm'] = $h ? (float)$h : null;
                $attributes['book_width_cm'] = $w ? (float)$w : null;
                if ($h && $w) {
                    $attributes['book_size'] = "{$h} cm × {$w} cm";
                } elseif ($h) {
                    $attributes['book_size'] = "{$h} cm (Height)";
                } elseif ($w) {
                    $attributes['book_size'] = "{$w} cm (Width)";
                }
            }

            if ($request->filled('sub_category_name')) {
                $attributes['sub_category_name'] = $request->input('sub_category_name');
            }
            if ($request->filled('ekushey_category')) {
                $attributes['ekushey_category'] = $request->input('ekushey_category');
            }
            if ($request->filled('genre_category')) {
                $attributes['genre_category'] = $request->input('genre_category');
            }
            if ($request->filled('audience_category')) {
                $attributes['audience_category'] = $request->input('audience_category');
            }
            if ($request->filled('look_inside_type')) {
                $attributes['look_inside_type'] = $request->input('look_inside_type');
            }
            if ($request->hasFile('look_inside_images')) {
                $imagePaths = [];
                $files = (array) $request->file('look_inside_images');
                foreach ($files as $imgFile) {
                    if ($imgFile instanceof \Illuminate\Http\UploadedFile) {
                        $imagePaths[] = $imgFile->store('books/look_inside', 'public');
                    }
                }
                if (!empty($imagePaths)) {
                    $attributes['look_inside_images'] = json_encode($imagePaths);
                }
            }
        }

        return $attributes;
    }

    /**
     * Store an uploaded file and return its public URL.
     *
     * @return string|null|false  the new value, or false to leave the column alone
     */
    private function handleUpload(Request $request, string $name, array $field, Model $record): string|null|false
    {
        if ($request->boolean("remove_{$name}")) {
            $this->deleteStoredFile($record->{$name} ?? null);

            return null;
        }

        $file = $request->file($name);

        if (! $file instanceof UploadedFile) {
            return false;
        }

        $path = $file->store($field['disk'] ?? 'uploads', self::UPLOAD_DISK);

        // Replacing a file should not leave the old one behind on disk.
        $this->deleteStoredFile($record->{$name} ?? null);

        return $path;
    }

    /**
     * Remove a previously uploaded file.
     *
     * Only paths under the public disk's own URL prefix are touched, so a legacy
     * value pointing at an external CDN is left alone.
     */
    private function deleteStoredFile(?string $value): void
    {
        if (! $value) {
            return;
        }

        $prefix = rtrim(Storage::disk(self::UPLOAD_DISK)->url(''), '/') . '/';

        if (! str_starts_with($value, $prefix)) {
            return;
        }

        $relative = substr($value, strlen($prefix));

        if ($relative !== '' && ! str_contains($relative, '..')) {
            Storage::disk(self::UPLOAD_DISK)->delete($relative);
        }
    }

    /**
     * Ownership / moderation columns.
     *
     * Anything an admin types in is approved on the spot — the approval queue
     * exists for submissions coming from sellers, publishers and authors.
     *
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    private function creditAttributes(Request $request, array $spec, bool $isNew, ?Model $record = null): array
    {
        $creditedUser = $request->integer('submitted_by') ?: null;
        $attributes   = [];

        if (Schema::hasColumn($spec['table'], 'mod_status')) {
            $attributes['owner_name']  = $request->input('owner_name') ?: null;
            $attributes['owner_phone'] = $request->input('owner_phone') ?: null;

            if ($isNew) {
                $attributes['submitted_by'] = $creditedUser ?? auth()->id();
                $attributes['mod_status']   = $request->input('mod_status', 'approved');
                $attributes['reviewed_by']  = auth()->id();
                $attributes['reviewed_at']  = now();
            } elseif ($creditedUser !== null) {
                $attributes['submitted_by'] = $creditedUser;
            }
        }

        // blog_posts.author_id is a NOT NULL FK to users.
        // Map author from authors/users table safely so SQLite/MySQL FK constraint never fails.
        if ($spec['table'] === 'blog_posts') {
            $rawAuthorId = $request->input('author_id');
            $authorUserId = null;
            $ownerName = $request->filled('owner_name') ? trim((string) $request->input('owner_name')) : null;

            if ($rawAuthorId) {
                if (str_starts_with((string) $rawAuthorId, 'author_')) {
                    $dirId = (int) str_replace('author_', '', (string) $rawAuthorId);
                    $authorRow = DB::table('authors')->where('id', $dirId)->first();
                    if ($authorRow) {
                        if (empty($ownerName)) {
                            $ownerName = $authorRow->name;
                        }
                        $matchingUser = DB::table('users')
                            ->where(function($q) use ($authorRow) {
                                if (!empty($authorRow->email)) $q->orWhere('email', $authorRow->email);
                                if (!empty($authorRow->phone)) $q->orWhere('phone', $authorRow->phone);
                                $q->orWhere('name', $authorRow->name);
                            })->first();

                        if ($matchingUser) {
                            $authorUserId = (int) $matchingUser->id;
                        } else {
                            $authorUserId = DB::table('users')->insertGetId([
                                'name'       => $authorRow->name,
                                'email'      => $authorRow->email ?: ('author_' . $authorRow->id . '@ideaabd.com'),
                                'phone'      => $authorRow->phone ?: ('01' . str_pad((string)$authorRow->id, 9, '0', STR_PAD_LEFT)),
                                'password'   => bcrypt(Str::random(16)),
                                'role'       => 'author',
                                'reg_type'   => 'author',
                                'reg_status' => 'approved',
                                'is_active'  => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                } elseif (str_starts_with((string) $rawAuthorId, 'user_')) {
                    $uId = (int) str_replace('user_', '', (string) $rawAuthorId);
                    $userRow = DB::table('users')->where('id', $uId)->first();
                    if ($userRow) {
                        $authorUserId = (int) $userRow->id;
                        if (empty($ownerName) && $isNew) {
                            $ownerName = $userRow->name;
                        }
                    }
                } else {
                    $uId = (int) $rawAuthorId;
                    $userRow = DB::table('users')->where('id', $uId)->first();
                    if ($userRow) {
                        $authorUserId = (int) $userRow->id;
                        if (empty($ownerName) && $isNew) {
                            $ownerName = $userRow->name;
                        }
                    }
                }
            }

            if ($authorUserId) {
                $attributes['author_id'] = (int) $authorUserId;
            } elseif (!$isNew && $record && !empty($record->author_id)) {
                $attributes['author_id'] = (int) $record->author_id;
            } else {
                $attributes['author_id'] = (int) ($creditedUser ?: (auth()->id() ?: 1));
            }

            if ($ownerName !== null && $ownerName !== '') {
                $attributes['owner_name'] = $ownerName;
            } elseif (!$isNew && $record && !empty($record->owner_name)) {
                $attributes['owner_name'] = $record->owner_name;
            }

            if ($isNew && ! $request->filled('status')) {
                $attributes['status'] = 'published';
                $attributes['mod_status'] = 'approved';
            }

            if ($request->input('status') === 'published') {
                $attributes['status'] = 'published';
                $attributes['mod_status'] = 'approved';
                $attributes['reviewed_by'] = auth()->id();
                $attributes['reviewed_at'] = now();
                if (empty($attributes['published_at']) && ($isNew || empty($record?->published_at))) {
                    $attributes['published_at'] = now();
                }
            } elseif ($request->filled('status')) {
                $attributes['status'] = $request->input('status');
                if ($request->input('status') === 'draft') {
                    $attributes['mod_status'] = 'pending';
                }
            }
        }

        if ($spec['table'] === 'webzines' && $request->boolean('is_published')) {
            $attributes['published_at'] = now();
        }

        return $attributes;
    }

    /** A slug that is unique within the type's own table. */
    private function uniqueSlug(array $spec, string $source, ?Model $ignore): string
    {
        $base = $this->bengaliToEnglish($source) ?: Str::slug(Str::random(8));
        $slug = $base;
        $n    = 1;

        while ($this->slugTaken($spec, $slug, $ignore)) {
            $slug = $base . '-' . (++$n);
        }

        return $slug;
    }

    private function bengaliToEnglish(string $text): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $text = str_replace($bengali, $english, $text);
        return Str::slug($text, '-', null);
    }

    private function slugTaken(array $spec, string $slug, ?Model $ignore): bool
    {
        $query = DB::table($spec['table'])->where('slug', $slug);

        if ($ignore) {
            $query->where('id', '!=', $ignore->getKey());
        }

        return $query->exists();
    }

    /**
     * Options for every select in the form, keyed by lookup table.
     *
     * @param  array<string, mixed>  $spec
     * @param  \Illuminate\Database\Eloquent\Model|null  $record
     * @return array<string, array<int|string, string>>
     */
    private function lookups(array $spec, ?Model $record = null): array
    {
        $labels  = ['categories' => 'name', 'publishers' => 'name', 'authors' => 'name', 'blog_categories' => 'name'];
        $lookups = [];

        foreach ($spec['fields'] as $field) {
            $table = $field['lookup'] ?? null;

            if (! $table || isset($lookups[$table]) || ! Schema::hasTable($table)) {
                continue;
            }

            if ($table === 'categories') {
                $categories = DB::table('categories')
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->get(['id', 'name', 'parent_id']);

                $cats = [];
                $parents = $categories->whereNull('parent_id');
                $children = $categories->whereNotNull('parent_id');

                foreach ($parents as $p) {
                    $cats[$p->id] = $p->name;
                    foreach ($children->where('parent_id', $p->id) as $child) {
                        $cats[$child->id] = '— ' . $child->name . ' (' . $p->name . ')';
                    }
                }
                foreach ($children as $c) {
                    if (!isset($cats[$c->id])) {
                        $cats[$c->id] = $c->name;
                    }
                }
                $lookups['categories'] = $cats;
                continue;
            }

            if ($table === 'parent_categories') {
                $lookups['parent_categories'] = DB::table('categories')
                    ->whereNull('parent_id')
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all();
                continue;
            }

            $column = $labels[$table] ?? 'name';

            $query = DB::table($table);

            $lookups[$table] = $query
                ->orderBy($column)
                ->limit(500)
                ->pluck($column, 'id')
                ->all();
        }

        if ($spec['key'] === 'blog') {
            $authorOptions = [];

            // 1. Registered Users (authors, admins, contributors, sellers)
            if (Schema::hasTable('users')) {
                $users = DB::table('users')
                    ->whereIn('role', [User::ROLE_AUTHOR, User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_PUBLISHER, User::ROLE_SELLER])
                    ->orWhereNotNull('reg_type')
                    ->orderBy('name')
                    ->get(['id', 'name', 'role', 'phone']);

                foreach ($users as $u) {
                    $roleLabel = ($u->role === 'author' || $u->role === User::ROLE_AUTHOR) ? 'নিবন্ধিত লেখক' : $u->role;
                    $phoneStr = !empty($u->phone) ? " ({$u->phone})" : '';
                    $authorOptions['user_' . $u->id] = "{$u->name} [{$roleLabel}{$phoneStr}]";
                }
            }

            // 2. Authors Directory
            if (Schema::hasTable('authors')) {
                $dirAuthors = DB::table('authors')
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->get(['id', 'name']);

                foreach ($dirAuthors as $da) {
                    $authorOptions['author_' . $da->id] = "{$da->name} [লেখক ডিরেক্টরি #{$da->id}]";
                }
            }

            // 3. Ensure current record's author is in the dropdown if editing
            if ($record) {
                if (!empty($record->author_id) && !isset($authorOptions['user_' . $record->author_id])) {
                    $existingUser = DB::table('users')->where('id', $record->author_id)->first();
                    if ($existingUser) {
                        $authorOptions['user_' . $existingUser->id] = "{$existingUser->name} [মূল পোস্টকারী]";
                    }
                }
            }

            $lookups['authors'] = $authorOptions;
        } elseif (in_array($spec['key'] ?? '', ['books', 'ebooks', 'webzines'], true) && Schema::hasTable('authors')) {
            $authorList = DB::table('authors')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
            if ($spec['table'] === 'webzines' && Schema::hasTable('users')) {
                $userList = DB::table('users')->orderBy('name')->pluck('name', 'id')->all();
                foreach ($userList as $uId => $uName) {
                    if (!in_array($uName, $authorList, true)) {
                        $authorList[$uId] = $uName;
                    }
                }
            }
            $lookups['authors'] = $authorList;
        }

        return $lookups;
    }

    /**
     * Synchronize Webzine Table of Contents Articles with page numbers.
     */
    private function syncWebzineArticles(Model $webzine, Request $request): void
    {
        if (!$request->has('toc_articles')) {
            return;
        }

        $rawArticles = $request->input('toc_articles', []);
        if (!is_array($rawArticles)) {
            return;
        }

        try {
            $existingIds = DB::table('webzine_articles')->where('webzine_id', $webzine->id)->pluck('id')->toArray();
            $keptIds = [];

            foreach ($rawArticles as $index => $item) {
                $title = trim((string) ($item['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $pageNumber = !empty($item['page_number']) ? (int) $item['page_number'] : ($index + 1);
                $authorId   = !empty($item['author_id']) ? (int) $item['author_id'] : null;
                $order      = !empty($item['order']) ? (int) $item['order'] : ($index + 1);
                $content    = (string) ($item['content'] ?? '');

                $articleId = !empty($item['id']) ? (int) $item['id'] : null;

                if ($articleId && in_array($articleId, $existingIds, true)) {
                    DB::table('webzine_articles')->where('id', $articleId)->update([
                        'title'       => $title,
                        'author_id'   => $authorId,
                        'page_number' => $pageNumber,
                        'order'       => $order,
                        'content'     => $content,
                        'updated_at'  => now(),
                    ]);
                    $keptIds[] = $articleId;
                } else {
                    $newId = DB::table('webzine_articles')->insertGetId([
                        'webzine_id'  => $webzine->id,
                        'title'       => $title,
                        'author_id'   => $authorId,
                        'page_number' => $pageNumber,
                        'order'       => $order,
                        'content'     => $content,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $keptIds[] = $newId;
                }
            }

            $toDelete = array_diff($existingIds, $keptIds);
            if (!empty($toDelete)) {
                DB::table('webzine_articles')->whereIn('id', $toDelete)->delete();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Webzine articles sync error: " . $e->getMessage());
        }
    }

    /**
     * Registered users an entry can be credited to.
     *
     * @return array<int, string>
     */
    private function creditees(): array
    {
        return User::query()
            ->whereIn('role', [
                User::ROLE_AUTHOR, User::ROLE_PUBLISHER, User::ROLE_SELLER,
                User::ROLE_SUB_ADMIN, User::ROLE_ADMIN,
            ])
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'role'])
            ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->role})"])
            ->all();
    }

    private function findRecord(array $spec, int $id): Model
    {
        $this->guardTableExists($spec);

        $query = $spec['model']::query();

        if ($this->softDeletes($spec['model'])) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /** @param  class-string<Model>  $model */
    private function softDeletes(string $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    private function guardTableExists(array $spec): void
    {
        abort_unless(
            Schema::hasTable($spec['table']),
            503,
            "{$spec['label']} টেবিল এখনো তৈরি হয়নি — সার্ভারে `php artisan migrate --force` চালান।"
        );
    }

    private function guardModeratable(Model $record): void
    {
        abort_unless(
            method_exists($record, 'markApproved') && $record->hasModerationColumns(),
            503,
            'মডারেশন কলাম এখনো তৈরি হয়নি — সার্ভারে `php artisan migrate --force` চালান।'
        );
    }

    /** Approve/reject also flips whichever "live on the site" flag the table uses. */
    private function setVisibility(Model $record, bool $visible): void
    {
        foreach (['is_active', 'is_published'] as $flag) {
            if (Schema::hasColumn($record->getTable(), $flag)) {
                $record->forceFill([$flag => $visible])->save();

                return;
            }
        }

        if (Schema::hasColumn($record->getTable(), 'status')) {
            $record->forceFill(['status' => $visible ? 'published' : 'draft'])->save();
        }
    }
}
