<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Book\Models\Category;
use Modules\Ebook\Models\Ebook;
use Modules\Publisher\Models\Publisher;

class AuthorEbookController extends Controller
{
    /**
     * Display Author's E-Book Inventory.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $query = Ebook::where(function ($q) use ($user, $author) {
            $q->where('author_user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->with(['category']);

        if ($request->filled('status')) {
            $query->where('mod_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $ebooks = $query->latest('id')->paginate(15)->withQueryString();

        return view('author.ebooks.index', compact('ebooks', 'author'));
    }

    /**
     * Show form to create new self-published E-Book.
     */
    public function create(): View
    {
        $author = auth()->user()->getAuthorRecord();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $publishers = Publisher::where('is_active', true)->orderBy('name')->get();

        return view('author.ebooks.create', compact('author', 'categories', 'publishers'));
    }

    /**
     * Quick Store new Category from Author Ebook create screen.
     */
    public function quickStoreCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ], [
            'name.required' => 'বিষয়ের নাম দিন।',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name) ?: 'cat-' . time();

        $category = Category::where('name', $name)->first();
        if (!$category) {
            $category = Category::create([
                'name'      => $name,
                'slug'      => $slug,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success'  => true,
            'id'       => $category->id,
            'name'     => $category->name,
            'message'  => 'নতুন বিষয়শ্রেণী ‘' . $category->name . '’ সফলভাবে যুক্ত হয়েছে!',
        ]);
    }

    /**
     * Store new self-published E-Book.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'subtitle'              => 'nullable|string|max:255',
            'isbn'                  => 'nullable|string|max:50',
            'category_id'           => 'required|exists:categories,id',
            'publisher_id'          => 'nullable|exists:publishers,id',
            'description'           => 'required|string|min:20',
            'price'                 => 'required|numeric|min:0',
            'discount_price'        => 'nullable|numeric|min:0|lt:price',
            'pages'                 => 'nullable|integer|min:1',
            'preview_page_limit'    => 'nullable|integer|min:1|max:100',
            'cover_image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'file_path'             => [
                'required',
                'file',
                'max:153600',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'epub'])) {
                        $fail('মূল ফাইলটি অবশ্যই PDF অথবা EPUB ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'epub_file_path'        => [
                'nullable',
                'file',
                'max:153600',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['epub'])) {
                        $fail('ই-পাব ফাইলটি অবশ্যই .epub ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'sample_file_path'      => [
                'nullable',
                'file',
                'max:102400',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'epub'])) {
                        $fail('নমুনা ফাইলটি অবশ্যই PDF অথবা EPUB ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'is_preorder'           => 'nullable|boolean',
            'preorder_release_date' => 'nullable|required_if:is_preorder,1|date|after:today',
        ], [
            'title.required'          => 'ই-বুকের শিরোনাম দিন।',
            'category_id.required'    => 'ই-বুকের ক্যাটাগরি নির্বাচন করুন।',
            'description.required'    => 'ই-বুকের বিবরণ বা পরিচিতি লিখুন (কমপক্ষে ২০ অক্ষর)।',
            'price.required'          => 'ই-বুকের বিক্রয়মূল্য নির্ধারণ করুন।',
            'cover_image.image'       => 'কাভার ফাইলটি অবশ্যই একটি ছবি (JPG, PNG, WebP) হতে হবে।',
            'cover_image.max'         => 'কাভার ছবির সাইজ সর্বোচ্চ ১৫ মেগাবাইট হতে পারবে।',
            'file_path.required'      => 'ই-বুকের মূল ডিজিটাল ফাইল (PDF/EPUB) আপলোড করুন।',
            'file_path.max'           => 'মূল ফাইল সাইজ সর্বোচ্চ ১৫০ মেগাবাইট হতে পারবে।',
            'sample_file_path.max'    => 'নমুনা ফাইল সাইজ সর্বোচ্চ ১০০ মেগাবাইট হতে পারবে।',
        ]);

        $authorName = $author?->name ?: $user->name;

        // Process Cover Image (Auto-crop 2:3 ratio or Auto-generate Soft Accent Cover)
        $aiTheme = $request->input('ai_cover_theme', 'ivory');
        $coverPath = $this->processCoverImage(
            $request->file('cover_image'), 
            $validated['title'], 
            $authorName,
            $aiTheme
        );

        // Upload Main File
        $mainFile = $request->file('file_path');
        $mainFilePath = $mainFile->store('ebooks/files', 'public');
        $fileType = strtolower($mainFile->getClientOriginalExtension());
        $fileSize = $mainFile->getSize();

        // Upload Optional EPUB
        $epubPath = null;
        if ($request->hasFile('epub_file_path')) {
            $epubPath = $request->file('epub_file_path')->store('ebooks/epubs', 'public');
        }

        // Preview Settings & Sample File
        $previewPageLimit = !empty($validated['preview_page_limit']) 
            ? (int) $validated['preview_page_limit'] 
            : \App\Support\SiteSetting::ebookPreviewLimit();

        $samplePath = null;
        if ($request->hasFile('sample_file_path')) {
            $samplePath = $request->file('sample_file_path')->store('ebooks/samples', 'public');
        } else {
            // Auto-link main file as sample source (Viewer will restrict to preview_page_limit)
            $samplePath = $mainFilePath;
        }

        // Generate Slug
        $slug = Str::slug($validated['title']) ?: 'ebook-' . time();
        if (Ebook::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        $ebook = Ebook::create([
            'author_user_id'        => $user->id,
            'author_id'             => $author?->id,
            'author_name'           => $authorName,
            'category_id'           => $validated['category_id'],
            'publisher_id'          => $validated['publisher_id'] ?? null,
            'title'                 => $validated['title'],
            'subtitle'              => $validated['subtitle'] ?? null,
            'isbn'                  => $validated['isbn'] ?? null,
            'slug'                  => $slug,
            'description'           => $validated['description'],
            'price'                 => $validated['price'],
            'discount_price'        => $validated['discount_price'] ?? null,
            'royalty_percentage'    => 50.00, // Standard 50% Author Share Model
            'cover_image'           => $coverPath,
            'file_path'             => $mainFilePath,
            'epub_file_path'        => $epubPath,
            'sample_file_path'      => $samplePath,
            'file_type'             => $fileType,
            'file_size'             => $fileSize,
            'pages'                 => !empty($validated['pages']) ? (int) $validated['pages'] : 1,
            'preview_page_limit'    => $previewPageLimit,
            'format'                => $epubPath ? 'epub,pdf' : $fileType,
            'drm_enabled'           => true,
            'is_preorder'           => !empty($validated['is_preorder']),
            'preorder_release_date' => $validated['preorder_release_date'] ?? null,
            'is_active'             => false, // Inactive until Admin Approval
            'mod_status'            => 'pending', // Pending Admin Moderation Queue
            'submitted_by'          => $user->id,
            'owner_name'            => $user->name,
            'owner_phone'           => $user->phone,
        ]);

        return redirect()->route('author.ebooks.index')
            ->with('success', 'ই-বুকটি সফলভাবে জমা হয়েছে! প্রচ্ছদ ও ১৬ পৃষ্ঠার প্রিভিউ প্রস্তুত করা হয়েছে। অ্যাডমিন পর্যালোচনার পর এটি স্টোরে লাইভ হবে।');
    }

    /**
     * Show form to edit Author's E-Book.
     */
    public function edit(int $id): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $ebook = Ebook::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('author_user_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $publishers = Publisher::where('is_active', true)->orderBy('name')->get();

        return view('author.ebooks.edit', compact('ebook', 'author', 'categories', 'publishers'));
    }

    /**
     * Update Author's E-Book.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $ebook = Ebook::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('author_user_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'subtitle'              => 'nullable|string|max:255',
            'isbn'                  => 'nullable|string|max:50',
            'category_id'           => 'required|exists:categories,id',
            'publisher_id'          => 'nullable|exists:publishers,id',
            'description'           => 'required|string|min:20',
            'price'                 => 'required|numeric|min:0',
            'discount_price'        => 'nullable|numeric|min:0|lt:price',
            'pages'                 => 'nullable|integer|min:1',
            'preview_page_limit'    => 'nullable|integer|min:1|max:50',
            'cover_image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'file_path'             => [
                'nullable',
                'file',
                'max:102400',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'epub'])) {
                        $fail('মূল ফাইলটি অবশ্যই PDF অথবা EPUB ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'epub_file_path'        => [
                'nullable',
                'file',
                'max:102400',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['epub'])) {
                        $fail('ই-পাব ফাইলটি অবশ্যই .epub ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'sample_file_path'      => [
                'nullable',
                'file',
                'max:51200',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'epub'])) {
                        $fail('নমুনা ফাইলটি অবশ্যই PDF অথবা EPUB ফরম্যাটের হতে হবে।');
                    }
                }
            ],
            'is_preorder'           => 'nullable|boolean',
            'preorder_release_date' => 'nullable|required_if:is_preorder,1|date',
        ], [
            'title.required'          => 'ই-বুকের শিরোনাম দিন।',
            'category_id.required'    => 'ই-বুকের ক্যাটাগরি নির্বাচন করুন।',
            'description.required'    => 'ই-বুকের বিবরণ বা পরিচিতি লিখুন (কমপক্ষে ২০ অক্ষর)।',
            'price.required'          => 'ই-বুকের বিক্রয়মূল্য নির্ধারণ করুন।',
            'cover_image.image'       => 'কাভার ফাইলটি অবশ্যই একটি ছবি (JPG, PNG, WebP) হতে হবে।',
            'cover_image.max'         => 'কাভার ছবির সাইজ সর্বোচ্চ ৮ মেগাবাইট হতে পারবে।',
            'file_path.max'           => 'মূল ফাইল সাইজ সর্বোচ্চ ১০০ মেগাবাইট হতে পারবে।',
            'sample_file_path.max'    => 'নমুনা ফাইল সাইজ সর্বোচ্চ ৫০ মেগাবাইট হতে পারবে।',
        ]);

        $authorName = $author?->name ?: $user->name;

        $data = [
            'category_id'           => $validated['category_id'],
            'publisher_id'          => $validated['publisher_id'] ?? null,
            'title'                 => $validated['title'],
            'subtitle'              => $validated['subtitle'] ?? null,
            'isbn'                  => $validated['isbn'] ?? null,
            'description'           => $validated['description'],
            'price'                 => $validated['price'],
            'discount_price'        => $validated['discount_price'] ?? null,
            'pages'                 => !empty($validated['pages']) ? (int) $validated['pages'] : ($ebook->pages ?: 1),
            'preview_page_limit'    => !empty($validated['preview_page_limit']) ? (int) $validated['preview_page_limit'] : ($ebook->preview_page_limit ?: \App\Support\SiteSetting::ebookPreviewLimit()),
            'is_preorder'           => !empty($validated['is_preorder']),
            'preorder_release_date' => $validated['preorder_release_date'] ?? null,
            'mod_status'            => 'pending', // Re-submits for moderation upon major edit
        ];

        if ($request->hasFile('cover_image')) {
            if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $data['cover_image'] = $this->processCoverImage(
                $request->file('cover_image'),
                $validated['title'],
                $authorName,
                $request->input('ai_cover_theme', 'ivory')
            );
        }

        if ($request->hasFile('file_path')) {
            $mainFile = $request->file('file_path');
            $data['file_path'] = $mainFile->store('ebooks/files', 'public');
            $data['file_type'] = strtolower($mainFile->getClientOriginalExtension());
            $data['file_size'] = $mainFile->getSize();
            if (!$request->hasFile('sample_file_path')) {
                $data['sample_file_path'] = $data['file_path'];
            }
        }

        if ($request->hasFile('epub_file_path')) {
            $data['epub_file_path'] = $request->file('epub_file_path')->store('ebooks/epubs', 'public');
        }

        if ($request->hasFile('sample_file_path')) {
            $data['sample_file_path'] = $request->file('sample_file_path')->store('ebooks/samples', 'public');
        }

        $ebook->update($data);

        return redirect()->route('author.ebooks.index')
            ->with('success', 'ই-বুক সফলভাবে আপডেট করা হয়েছে এবং পর্যালোচনার জন্য জমা দেওয়া হয়েছে।');
    }

    /**
     * Delete Author's E-Book (if draft or rejected).
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $ebook = Ebook::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('author_user_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        $ebook->delete();

        return redirect()->route('author.ebooks.index')
            ->with('success', 'ই-বুকটি তালিকা থেকে অপসারণ করা হয়েছে।');
    }

    /**
     * Process uploaded cover (auto-crop to 2:3 ratio) or generate default aesthetic SVG cover.
     */
    protected function processCoverImage(?\Illuminate\Http\UploadedFile $file, string $title, string $authorName): string
    {
        if ($file && $file->isValid()) {
            // Check if GD extension is available for auto 2:3 cropping
            if (extension_loaded('gd')) {
                try {
                    $srcData = file_get_contents($file->getRealPath());
                    $src = @imagecreatefromstring($srcData);
                    if ($src) {
                        $srcW = imagesx($src);
                        $srcH = imagesy($src);

                        // Target 2:3 Aspect Ratio (800 x 1200)
                        $targetW = 800;
                        $targetH = 1200;
                        $targetRatio = $targetW / $targetH;
                        $srcRatio = $srcW / $srcH;

                        if ($srcRatio > $targetRatio) {
                            // Source is wider than 2:3: crop sides
                            $cropW = (int) round($srcH * $targetRatio);
                            $cropH = $srcH;
                            $srcX = (int) round(($srcW - $cropW) / 2);
                            $srcY = 0;
                        } else {
                            // Source is taller than 2:3: crop top/bottom
                            $cropW = $srcW;
                            $cropH = (int) round($srcW / $targetRatio);
                            $srcX = 0;
                            $srcY = (int) round(($srcH - $cropH) / 2);
                        }

                        $dst = imagecreatetruecolor($targetW, $targetH);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);

                        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);

                        $filename = 'cover_' . time() . '_' . Str::random(8) . '.jpg';
                        $fullPath = storage_path('app/public/ebooks/covers/' . $filename);
                        
                        if (!file_exists(dirname($fullPath))) {
                            mkdir(dirname($fullPath), 0755, true);
                        }

                        imagejpeg($dst, $fullPath, 92);
                        imagedestroy($src);
                        imagedestroy($dst);

                        return 'ebooks/covers/' . $filename;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Cover processing exception: " . $e->getMessage());
                }
    /**
     * Crop or process cover image to 2:3 ratio or auto-generate AI aesthetic cover.
     */
    protected function processCoverImage(?UploadedFile $file, string $title, string $authorName, string $aiTheme = 'ivory'): string
    {
        if ($file && $file->isValid()) {
            if (extension_loaded('gd')) {
                try {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $sourcePath = $file->getRealPath();

                    $src = match ($ext) {
                        'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
                        'png'         => @imagecreatefrompng($sourcePath),
                        'webp'        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
                        default       => null,
                    };

                    if ($src) {
                        $origW = imagesx($src);
                        $origH = imagesy($src);

                        $targetW = 800;
                        $targetH = 1200; // 2:3 Ratio

                        $targetRatio = 2 / 3;
                        $origRatio   = $origW / $origH;

                        if ($origRatio > $targetRatio) {
                            $cropH = $origH;
                            $cropW = (int) ($origH * $targetRatio);
                            $srcX  = (int) (($origW - $cropW) / 2);
                            $srcY  = 0;
                        } else {
                            $cropW = $origW;
                            $cropH = (int) ($origW / $targetRatio);
                            $srcX  = 0;
                            $srcY  = (int) (($origH - $cropH) / 2);
                        }

                        $dst = imagecreatetruecolor($targetW, $targetH);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);

                        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);

                        $filename = 'cover_' . time() . '_' . Str::random(8) . '.jpg';
                        $fullPath = storage_path('app/public/ebooks/covers/' . $filename);

                        if (!file_exists(dirname($fullPath))) {
                            mkdir(dirname($fullPath), 0755, true);
                        }

                        imagejpeg($dst, $fullPath, 92);
                        imagedestroy($src);
                        imagedestroy($dst);

                        return 'ebooks/covers/' . $filename;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Cover processing exception: " . $e->getMessage());
                }
            }

            return $file->store('ebooks/covers', 'public');
        }

        // Auto-generate high-contrast soft-shade aesthetic SVG cover with selected AI Theme
        return $this->generateDefaultEbookCover($title, $authorName, $aiTheme);
    }

    /**
     * Generate luxury Light/Soft Accent SVG e-book cover with clear, high-contrast typography.
     */
    protected function generateDefaultEbookCover(string $title, string $authorName, string $aiTheme = 'ivory'): string
    {
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (mb_strlen($currentLine . ' ' . $word) <= 18) {
                $currentLine = trim($currentLine . ' ' . $word);
            } else {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        // Limit to 3 lines maximum
        $lines = array_slice($lines, 0, 3);
        $lineCount = count($lines);

        $palette = match ($aiTheme) {
            'linen' => [
                'bg1' => '#fdfcf9', 'bg2' => '#f4f0e6', 'bg3' => '#e9e1cf',
                'accent1' => '#475569', 'accent2' => '#64748b',
                'text' => '#1e293b', 'badgeBg' => '#f1f5f9',
            ],
            'mint' => [
                'bg1' => '#f0fdf4', 'bg2' => '#dcfce7', 'bg3' => '#bbf7d0',
                'accent1' => '#15803d', 'accent2' => '#16a34a',
                'text' => '#14532d', 'badgeBg' => '#ffffff',
            ],
            'gold' => [
                'bg1' => '#fffbeb', 'bg2' => '#fef3c7', 'bg3' => '#fde68a',
                'accent1' => '#b45309', 'accent2' => '#d97706',
                'text' => '#78350f', 'badgeBg' => '#ffffff',
            ],
            default => [ // Ivory
                'bg1' => '#fffefb', 'bg2' => '#fbf7ef', 'bg3' => '#f2eadb',
                'accent1' => '#b45309', 'accent2' => '#d97706',
                'text' => '#0f172a', 'badgeBg' => '#ffffff',
            ],
        };

        $titleSvg = '';
        $startY = match($lineCount) {
            1 => 480,
            2 => 440,
            3 => 410,
            default => 480,
        };
        $fontSize = match($lineCount) {
            1 => 44,
            2 => 38,
            3 => 32,
            default => 40,
        };

        foreach ($lines as $idx => $line) {
            $escaped = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
            $y = $startY + ($idx * ($fontSize + 14));
            $titleSvg .= "<text x=\"400\" y=\"{$y}\" fill=\"{$palette['text']}\" font-family=\"'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif\" font-size=\"{$fontSize}\" font-weight=\"800\" text-anchor=\"middle\">{$escaped}</text>\n  ";
        }

        $safeAuthor = htmlspecialchars(Str::limit($authorName, 40), ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1200" width="800" height="1200">
  <defs>
    <linearGradient id="softBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$palette['bg1']}" />
      <stop offset="50%" stop-color="{$palette['bg2']}" />
      <stop offset="100%" stop-color="{$palette['bg3']}" />
    </linearGradient>
    
    <radialGradient id="ambientGlow" cx="50%" cy="40%" r="65%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.9" />
      <stop offset="100%" stop-color="{$palette['bg3']}" stop-opacity="0.3" />
    </radialGradient>

    <linearGradient id="goldAcc" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="{$palette['accent1']}" />
      <stop offset="50%" stop-color="{$palette['accent2']}" />
      <stop offset="100%" stop-color="{$palette['accent1']}" />
    </linearGradient>

    <filter id="cardShadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="6" stdDeviation="10" flood-color="#1e293b" flood-opacity="0.07" />
    </filter>
  </defs>

  <!-- Background Base (Soft Theme) -->
  <rect width="800" height="1200" fill="url(#softBg)" />
  <rect width="800" height="1200" fill="url(#ambientGlow)" />

  <!-- Outer Classic Double Border Frame -->
  <rect x="36" y="36" width="728" height="1128" fill="none" stroke="{$palette['accent2']}" stroke-width="1.5" opacity="0.45" rx="10" />
  <rect x="48" y="48" width="704" height="1104" fill="none" stroke="{$palette['accent1']}" stroke-width="2.5" opacity="0.85" rx="8" />

  <!-- Ornate Corner Filigrees -->
  <path d="M 48 85 L 85 48 M 48 100 L 100 48" stroke="{$palette['accent2']}" stroke-width="2" opacity="0.75" />
  <path d="M 752 85 L 715 48 M 752 100 L 700 48" stroke="{$palette['accent2']}" stroke-width="2" opacity="0.75" />
  <path d="M 48 1115 L 85 1152 M 48 1100 L 100 1152" stroke="{$palette['accent2']}" stroke-width="2" opacity="0.75" />
  <path d="M 752 1115 L 715 1152 M 752 1100 L 700 1152" stroke="{$palette['accent2']}" stroke-width="2" opacity="0.75" />

  <!-- Top Header Crest Badge -->
  <rect x="270" y="88" width="260" height="42" rx="21" fill="{$palette['badgeBg']}" stroke="{$palette['accent2']}" stroke-width="1.5" filter="url(#cardShadow)" />
  <text x="400" y="115" fill="{$palette['accent1']}" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="16" font-weight="bold" text-anchor="middle" letter-spacing="1">✦ ডিজিটাল ই-বুক সংস্করণ ✦</text>

  <!-- Book Icon Crest -->
  <circle cx="400" cy="235" r="46" fill="{$palette['badgeBg']}" stroke="{$palette['accent1']}" stroke-width="1.5" filter="url(#cardShadow)" />
  <path d="M 380 225 Q 400 215 400 247 Q 400 215 420 225 L 420 253 Q 400 243 400 257 Q 400 243 380 253 Z" fill="{$palette['accent2']}" />

  <!-- Multi-line Centered Crisp Title -->
  {$titleSvg}

  <!-- Decorative Golden Filigree Divider -->
  <line x1="220" y1="620" x2="350" y2="620" stroke="url(#goldAcc)" stroke-width="2" />
  <text x="400" y="627" fill="{$palette['accent1']}" font-size="20" text-anchor="middle">❖ ── ✦ ── ❖</text>
  <line x1="450" y1="620" x2="580" y2="620" stroke="url(#goldAcc)" stroke-width="2" />

  <!-- Author Section (High-Contrast Clean Text) -->
  <rect x="180" y="740" width="440" height="68" rx="34" fill="{$palette['badgeBg']}" stroke="#cbd5e1" stroke-width="1.5" filter="url(#cardShadow)" />
  <text x="400" y="782" fill="{$palette['text']}" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="24" font-weight="bold" text-anchor="middle">✍️ রচনা: {$safeAuthor}</text>

  <!-- Bottom Publisher Seal & Brand Bar -->
  <line x1="80" y1="1020" x2="720" y2="1020" stroke="#cbd5e1" stroke-width="1.5" />
  <text x="100" y="1070" fill="#475569" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="18" font-weight="bold">আইডিয়া প্রকাশন (IDEA Publication)</text>
  <text x="700" y="1070" fill="{$palette['accent1']}" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="16" font-weight="bold" text-anchor="end">www.ideaabd.com</text>
</svg>
SVG;

        $fileName = 'cover_auto_' . time() . '_' . Str::random(8) . '.svg';
        Storage::disk('public')->put('ebooks/covers/' . $fileName, $svg);
        return 'ebooks/covers/' . $fileName;
    }
}
