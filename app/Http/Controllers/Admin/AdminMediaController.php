<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class AdminMediaController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Folder definitions mapping keys to their disk paths.
     */
    private function getFolderDefinitions(): array
    {
        $storagePublic = storage_path('app/public');
        $publicImages = public_path('images');

        return [
            'books' => [
                'label' => 'বই ও কাভার',
                'icon' => 'fa-solid fa-book-open',
                'dirs' => [
                    $storagePublic . '/books',
                    $storagePublic . '/books/covers',
                    $publicImages . '/books',
                ],
                'default_upload' => $storagePublic . '/books',
            ],
            'banners' => [
                'label' => 'ব্যানার ও ক্যাম্পেইন',
                'icon' => 'fa-solid fa-images',
                'dirs' => [
                    $publicImages . '/banners',
                    $storagePublic . '/campaigns',
                ],
                'default_upload' => $publicImages . '/banners',
            ],
            'settings' => [
                'label' => 'ব্র্যান্ডিং ও সেটিংস',
                'icon' => 'fa-solid fa-gear',
                'dirs' => [
                    $publicImages . '/settings',
                    $storagePublic . '/settings',
                ],
                'default_upload' => $publicImages . '/settings',
            ],
            'authors' => [
                'label' => 'লেখক ও গবেষক',
                'icon' => 'fa-solid fa-user-pen',
                'dirs' => [
                    $storagePublic . '/authors',
                    $publicImages . '/authors',
                ],
                'default_upload' => $storagePublic . '/authors',
            ],
            'blog' => [
                'label' => 'ব্লগ ও ফিচার',
                'icon' => 'fa-solid fa-newspaper',
                'dirs' => [
                    $storagePublic . '/blog',
                    $publicImages . '/blog',
                ],
                'default_upload' => $storagePublic . '/blog',
            ],
            'payments' => [
                'label' => 'পেমেন্ট ও QR কোড',
                'icon' => 'fa-solid fa-qrcode',
                'dirs' => [
                    $storagePublic . '/settings/qrcodes',
                    $publicImages . '/payments',
                ],
                'default_upload' => $storagePublic . '/settings/qrcodes',
            ],
            'avatars' => [
                'label' => 'ইউজার অ্যাভাটার',
                'icon' => 'fa-solid fa-circle-user',
                'dirs' => [
                    $storagePublic . '/avatars',
                ],
                'default_upload' => $storagePublic . '/avatars',
            ],
            'ebooks' => [
                'label' => 'ই-বুক অ্যাসেট',
                'icon' => 'fa-solid fa-file-pdf',
                'dirs' => [
                    $storagePublic . '/ebooks',
                ],
                'default_upload' => $storagePublic . '/ebooks',
            ],
            'signatures' => [
                'label' => 'স্বাক্ষর ও ডকুমেন্টস',
                'icon' => 'fa-solid fa-signature',
                'dirs' => [
                    $storagePublic . '/signatures',
                ],
                'default_upload' => $storagePublic . '/signatures',
            ],
            'uploads' => [
                'label' => 'সাধারণ আপলোড',
                'icon' => 'fa-solid fa-cloud-arrow-up',
                'dirs' => [
                    $storagePublic . '/uploads',
                    $storagePublic . '/images',
                ],
                'default_upload' => $storagePublic . '/uploads',
            ],
            'general' => [
                'label' => 'রুট মিডিয়া',
                'icon' => 'fa-solid fa-folder',
                'dirs' => [
                    $publicImages,
                ],
                'default_upload' => $publicImages,
            ],
        ];
    }

    /**
     * Display media library files.
     */
    public function index(Request $request): View
    {
        $folderFilter = $request->string('folder')->trim()->value() ?: 'all';
        $formatFilter = $request->string('format')->trim()->value() ?: 'all';
        $dimensionFilter = $request->string('dim')->trim()->value() ?: 'all';
        $sort = $request->string('sort')->trim()->value() ?: 'latest';
        $viewMode = $request->string('view')->trim()->value() ?: 'grid';
        $search = $request->string('search')->trim()->value();

        $storagePublic = storage_path('app/public');
        $publicImages = public_path('images');

        // Title & Model association lookup (Cached for fast retrieval)
        $titleLookup = \Illuminate\Support\Facades\Cache::remember('media_asset_title_lookup_v2', 300, function () {
            $map = [];

            // Books
            if (\Illuminate\Support\Facades\Schema::hasTable('books')) {
                $books = \Illuminate\Support\Facades\DB::table('books')->whereNotNull('cover_image')->get(['title', 'author_name', 'cover_image', 'slug']);
                foreach ($books as $b) {
                    $base = basename((string) $b->cover_image);
                    $baseNoExt = pathinfo($base, PATHINFO_FILENAME);
                    $info = ['title' => $b->title, 'subtitle' => $b->author_name ?: 'আইডিয়া প্রকাশন', 'type' => 'বই', 'link' => url('/books/' . ($b->slug ?: $b->title))];
                    $map[$base] = $info;
                    $map[$baseNoExt] = $info;
                }
            }

            // Authors
            if (\Illuminate\Support\Facades\Schema::hasTable('authors')) {
                $authors = \Illuminate\Support\Facades\DB::table('authors')->whereNotNull('avatar')->get(['name', 'name_bn', 'avatar', 'slug']);
                foreach ($authors as $a) {
                    $base = basename((string) $a->avatar);
                    $baseNoExt = pathinfo($base, PATHINFO_FILENAME);
                    $info = ['title' => $a->name_bn ?: $a->name, 'subtitle' => 'লেখক / গবেষক', 'type' => 'লেখক', 'link' => url('/authors/' . ($a->slug ?: $a->name))];
                    $map[$base] = $info;
                    $map[$baseNoExt] = $info;
                }
            }

            // Blog Posts
            if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                $posts = \Illuminate\Support\Facades\DB::table('blog_posts')->whereNotNull('featured_image')->get(['title', 'featured_image', 'slug']);
                foreach ($posts as $p) {
                    $base = basename((string) $p->featured_image);
                    $baseNoExt = pathinfo($base, PATHINFO_FILENAME);
                    $info = ['title' => $p->title, 'subtitle' => 'ব্লগ ও প্রবন্ধ', 'type' => 'ব্লগ', 'link' => url('/blog/' . ($p->slug ?: $p->title))];
                    $map[$base] = $info;
                    $map[$baseNoExt] = $info;
                }
            }

            return $map;
        });

        $folderDefs = $this->getFolderDefinitions();

        $mediaItems = [];
        $totalBytes = 0;
        $webpCount = 0;
        $folderStats = [];

        // Initialize folder counters
        foreach ($folderDefs as $k => $fInfo) {
            $folderStats[$k] = [
                'count' => 0,
                'bytes' => 0,
                'formatted' => '0 B',
                'label' => $fInfo['label'],
                'icon' => $fInfo['icon'],
            ];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico', 'bmp', 'avif'];
        $scannedPaths = [];

        foreach ($folderDefs as $folderKey => $folderConfig) {
            foreach ($folderConfig['dirs'] as $dir) {
                if (!File::isDirectory($dir)) {
                    continue;
                }

                $files = ($dir === $publicImages) ? File::files($dir) : File::allFiles($dir);

                foreach ($files as $file) {
                    $pathname = $file->getPathname();
                    if (isset($scannedPaths[$pathname])) {
                        continue;
                    }
                    $scannedPaths[$pathname] = true;

                    $ext = strtolower($file->getExtension());
                    if (!in_array($ext, $allowedExtensions)) {
                        continue;
                    }

                    $filename = $file->getFilename();
                    $size = $file->getSize();

                    // Accumulate stats
                    $totalBytes += $size;
                    $folderStats[$folderKey]['count']++;
                    $folderStats[$folderKey]['bytes'] += $size;

                    if ($ext === 'webp') {
                        $webpCount++;
                    }

                    // Apply Folder Filter
                    if ($folderFilter !== 'all' && $folderFilter !== $folderKey) {
                        continue;
                    }

                    // Apply Format Filter
                    if ($formatFilter !== 'all') {
                        if ($formatFilter === 'jpg' && !in_array($ext, ['jpg', 'jpeg'])) {
                            continue;
                        } elseif ($formatFilter !== 'jpg' && $ext !== $formatFilter) {
                            continue;
                        }
                    }

                    // Apply Search Filter
                    $filenameBase = pathinfo($filename, PATHINFO_FILENAME);
                    $itemInfo = $titleLookup[$filename] ?? ($titleLookup[$filenameBase] ?? null);
                    
                    if (!$itemInfo) {
                        // Smart auto-formatter for human-friendly titles
                        $cleanBase = preg_replace('/[_-]/', ' ', $filenameBase);
                        $isHash = (strlen($filenameBase) > 20 && !str_contains($cleanBase, ' '));

                        $fallbackTitle = match($folderKey) {
                            'books'       => $isHash ? 'বইয়ের প্রচ্ছদ (Book Cover)' : Str::headline($cleanBase),
                            'banners'     => $isHash ? 'প্রমোশনাল ব্যানার (Banner)' : Str::headline($cleanBase),
                            'avatars'     => 'ইউজার প্রোফাইল ছবি (Avatar)',
                            'payments'    => 'পেমেন্ট গেটওয়ে QR কোড',
                            'ebooks'      => 'ডিজিটাল ই-বুক অ্যাসেট',
                            'signatures'  => 'ডিজিটাল স্বাক্ষর ও সিল',
                            'publishers'  => 'প্রকাশনীর অফিসিয়াল লোগো',
                            'brands'      => 'ব্র্যান্ডিং ও ট্রেডমার্ক আইকন',
                            default       => $isHash ? 'মিডিয়া অ্যাসেট' : Str::headline($cleanBase),
                        };

                        $fallbackSubtitle = match($folderKey) {
                            'books'       => 'আইডিয়া প্রকাশন',
                            'banners'     => 'মার্কেটিং ও ক্যাম্পেইন',
                            'avatars'     => 'প্রোফাইল পিকচার',
                            'payments'    => 'পেমেন্ট গেটওয়ে',
                            'ebooks'      => 'ই-বুক লাইব্রেরি',
                            default       => $folderDefs[$folderKey]['label'] ?? 'অ্যাসেট লাইব্রেরি',
                        };

                        $itemTitle = $fallbackTitle;
                        $itemSubtitle = $fallbackSubtitle;
                    } else {
                        $itemTitle = $itemInfo['title'] ?? null;
                        $itemSubtitle = $itemInfo['subtitle'] ?? null;
                    }
                    $itemLink = $itemInfo['link'] ?? null;

                    if ($search) {
                        $searchLower = strtolower($search);
                        $matched = str_contains(strtolower($filename), $searchLower)
                            || str_contains(strtolower($folderKey), $searchLower)
                            || ($itemTitle && str_contains(strtolower($itemTitle), $searchLower))
                            || ($itemSubtitle && str_contains(strtolower($itemSubtitle), $searchLower));
                        if (!$matched) {
                            continue;
                        }
                    }

                    // Generate Web URL
                    $relStorage = str_replace([$storagePublic, $publicImages, public_path()], '', $pathname);
                    $relClean = str_replace('\\', '/', $relStorage);

                    if (str_starts_with($pathname, $storagePublic)) {
                        $url = asset('storage' . str_replace('\\', '/', str_replace($storagePublic, '', $pathname)));
                    } else {
                        $url = asset(ltrim($relClean, '/'));
                    }

                    // Extract Dimensions & Metadata (Cached per file mtime for ultra-fast page speed)
                    $mtime = $file->getMTime();
                    $cacheKey = 'media_dim_' . md5($pathname) . '_' . $mtime;

                    $dimMeta = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($pathname, $ext) {
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'])) {
                            return ['w' => null, 'h' => null, 'ratio' => 'Vector'];
                        }
                        $imgInfo = @getimagesize($pathname);
                        if (!$imgInfo || $imgInfo[0] <= 0 || $imgInfo[1] <= 0) {
                            return ['w' => null, 'h' => null, 'ratio' => 'Auto'];
                        }
                        $w = $imgInfo[0];
                        $h = $imgInfo[1];
                        $r = round($w / $h, 2);
                        $aspect = match(true) {
                            $w === $h => '1:1',
                            $r >= 1.75 && $r <= 1.8 => '16:9',
                            $r >= 1.3 && $r <= 1.35 => '4:3',
                            $r >= 1.48 && $r <= 1.52 => '3:2',
                            $r >= 2.0 => 'Banner',
                            $r < 0.8 => 'Portrait',
                            default => "{$w}x{$h}",
                        };
                        return ['w' => $w, 'h' => $h, 'ratio' => $aspect];
                    });

                    $width = $dimMeta['w'] ?? null;
                    $height = $dimMeta['h'] ?? null;
                    $aspectRatio = $dimMeta['ratio'] ?? 'Auto';

                    // Apply Dimension Filter
                    if ($dimensionFilter !== 'all') {
                        if ($dimensionFilter === 'banner' && ($width === null || $width < 1200)) {
                            continue;
                        } elseif ($dimensionFilter === 'square' && ($width === null || $height === null || abs($width - $height) > 25)) {
                            continue;
                        } elseif ($dimensionFilter === 'thumb' && ($width === null || $width > 400)) {
                            continue;
                        } elseif ($dimensionFilter === 'portrait' && ($width === null || $height === null || $height <= $width)) {
                            continue;
                        }
                    }

                    $mime = match ($ext) {
                        'webp' => 'image/webp',
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'svg' => 'image/svg+xml',
                        'gif' => 'image/gif',
                        'avif' => 'image/avif',
                        'ico' => 'image/x-icon',
                        default => 'image/' . $ext,
                    };

                    $mediaItems[] = [
                        'filename'      => $filename,
                        'folder'        => $folderKey,
                        'folder_label'  => $folderDefs[$folderKey]['label'],
                        'folder_icon'   => $folderDefs[$folderKey]['icon'],
                        'path'          => $pathname,
                        'url'           => $url,
                        'size'          => $this->formatBytes($size),
                        'size_bytes'    => $size,
                        'ext'           => $ext,
                        'width'         => $width,
                        'height'        => $height,
                        'aspect_ratio'  => $aspectRatio,
                        'mime'          => $mime,
                        'item_title'    => $itemTitle,
                        'item_subtitle' => $itemSubtitle,
                        'item_link'     => $itemLink,
                        'updated_at'    => Carbon::createFromTimestamp($mtime),
                        'is_webp'       => ($ext === 'webp'),
                    ];
                }
            }
        }

        // Format folder storage bytes
        foreach ($folderStats as $k => &$fs) {
            $fs['formatted'] = $this->formatBytes($fs['bytes']);
        }
        unset($fs);

        // Sorting
        match ($sort) {
            'oldest'    => usort($mediaItems, fn ($a, $b) => $a['updated_at']->timestamp <=> $b['updated_at']->timestamp),
            'size_desc' => usort($mediaItems, fn ($a, $b) => $b['size_bytes'] <=> $a['size_bytes']),
            'size_asc'  => usort($mediaItems, fn ($a, $b) => $a['size_bytes'] <=> $b['size_bytes']),
            'name_asc'  => usort($mediaItems, fn ($a, $b) => strcasecmp($a['filename'], $b['filename'])),
            'name_desc' => usort($mediaItems, fn ($a, $b) => strcasecmp($b['filename'], $a['filename'])),
            default     => usort($mediaItems, fn ($a, $b) => $b['updated_at']->timestamp <=> $a['updated_at']->timestamp),
        };

        $totalFormatted = $this->formatBytes($totalBytes);
        $totalCount = count($scannedPaths);
        $filteredCount = count($mediaItems);
        $webpPercent = $totalCount > 0 ? round(($webpCount / $totalCount) * 100, 1) : 0;
        $gdLoaded = extension_loaded('gd');

        // Pagination controls
        $perPage = $request->input('per_page', '48');
        $currentPage = max(1, (int) $request->input('page', 1));

        if ($perPage !== 'all') {
            $perPageInt = max(12, (int) $perPage);
            $totalPages = max(1, (int) ceil($filteredCount / $perPageInt));
            $currentPage = min($currentPage, $totalPages);
            $offset = ($currentPage - 1) * $perPageInt;
            $paginatedItems = array_slice($mediaItems, $offset, $perPageInt);
        } else {
            $paginatedItems = $mediaItems;
            $totalPages = 1;
            $currentPage = 1;
        }

        return view('admin.media', compact(
            'mediaItems',
            'paginatedItems',
            'totalCount',
            'filteredCount',
            'totalFormatted',
            'webpCount',
            'webpPercent',
            'folderFilter',
            'formatFilter',
            'dimensionFilter',
            'sort',
            'viewMode',
            'search',
            'perPage',
            'currentPage',
            'totalPages',
            'folderDefs',
            'folderStats',
            'gdLoaded'
        ));
    }

    /**
     * Upload new media asset(s) with multi-file support and auto-optimization.
     */
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'files.*'   => 'nullable|image|mimes:jpeg,png,jpg,webp,svg,gif,ico,bmp,avif|max:10240',
            'file'      => 'nullable|image|mimes:jpeg,png,jpg,webp,svg,gif,ico,bmp,avif|max:10240',
            'folder'    => 'nullable|string',
            'auto_webp' => 'nullable|boolean',
            'max_dim'   => 'nullable|integer|in:800,1200,1920,0',
        ]);

        $folder = $request->input('folder', 'uploads');
        $folderDefs = $this->getFolderDefinitions();

        $targetDir = $folderDefs[$folder]['default_upload'] ?? storage_path('app/public/uploads');

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $uploadedFiles = $request->file('files') ?: ($request->file('file') ? [$request->file('file')] : []);

        if (empty($uploadedFiles)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'কোনো ফাইল পাওয়া যায়নি।'], 422);
            }
            return back()->with('error', 'কোনো ফাইল নির্বাচন করা হয়নি।');
        }

        $uploadedResults = [];
        $autoWebp = $request->boolean('auto_webp', false);
        $maxDim = (int) $request->input('max_dim', 1920);

        foreach ($uploadedFiles as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $slugName = Str::slug($origName) ?: 'media';
            $ext = strtolower($file->getClientOriginalExtension());
            $finalName = $slugName . '_' . substr(uniqid(), -6) . '.' . $ext;

            $file->move($targetDir, $finalName);
            $destinationPath = $targetDir . '/' . $finalName;

            // Auto-optimize uploaded image
            $this->optimizeImageFile($destinationPath, $maxDim, $autoWebp);

            $uploadedResults[] = [
                'filename' => basename($destinationPath),
                'path'     => $destinationPath,
            ];
        }

        if ($this->accessService) {
            $count = count($uploadedResults);
            $this->accessService->log('upload_media', "মিডিয়া লাইব্রেরিতে {$count}টি ফাইল আপলোড ও অপ্টিমাইজ করা হয়েছে");
        }

        $msg = count($uploadedResults) . 'টি ফাইল সফলভাবে আপলোড ও অপ্টিমাইজ সম্পন্ন হয়েছে!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items'   => $uploadedResults,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Save customized image from Studio (HTML5 Canvas Base64 Payload).
     */
    public function saveCustomized(Request $request): JsonResponse
    {
        $request->validate([
            'data_url'      => 'required|string',
            'original_path' => 'nullable|string',
            'mode'          => 'required|string|in:overwrite,new_copy',
            'new_filename'  => 'nullable|string',
            'folder'        => 'nullable|string',
            'target_format' => 'nullable|string|in:webp,png,jpg,jpeg',
        ]);

        $dataUrl = $request->input('data_url');
        $mode = $request->input('mode');
        $originalPath = $request->input('original_path');
        $newFilename = $request->input('new_filename');
        $targetFormat = $request->input('target_format', 'webp');

        // Extract base64 image data
        if (!preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
            return response()->json(['success' => false, 'message' => 'অবৈধ ইমেজ ডাটা ফরম্যাট!'], 422);
        }

        $imageData = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            return response()->json(['success' => false, 'message' => 'ইমেজ ডাটা ডিকোড ব্যর্থ হয়েছে।'], 422);
        }

        $folderDefs = $this->getFolderDefinitions();
        $targetFolder = $request->input('folder', 'uploads');

        if ($mode === 'overwrite' && $originalPath && File::exists($originalPath)) {
            $savePath = $originalPath;
        } else {
            $targetDir = $folderDefs[$targetFolder]['default_upload'] ?? storage_path('app/public/uploads');
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            $baseName = $newFilename ? Str::slug(pathinfo($newFilename, PATHINFO_FILENAME)) : 'customized_' . substr(uniqid(), -6);
            $ext = strtolower($targetFormat ?: 'webp');
            $savePath = $targetDir . '/' . $baseName . '.' . $ext;
        }

        // Security check
        if (!$this->isSafePath($savePath)) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত ফাইল পাথ এক্সেস!'], 403);
        }

        File::put($savePath, $imageData);

        if ($this->accessService) {
            $this->accessService->log('customize_media', "মিডিয়া স্টুডিওতে ছবি কাস্টমাইজ ও সংরক্ষণ করা হয়েছে: " . basename($savePath));
        }

        // Generate web URL
        $storagePublic = storage_path('app/public');
        $publicImages = public_path('images');

        if (str_starts_with($savePath, $storagePublic)) {
            $url = asset('storage' . str_replace('\\', '/', str_replace($storagePublic, '', $savePath)));
        } else {
            $url = asset(ltrim(str_replace([$publicImages, public_path()], '', $savePath), '/\\'));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'কাস্টমাইজড ছবি সফলভাবে সংরক্ষণ করা হয়েছে!',
            'url'      => $url,
            'filename' => basename($savePath),
            'size'     => $this->formatBytes(File::size($savePath)),
        ]);
    }

    /**
     * Rename a media file safely.
     */
    public function renameFile(Request $request): JsonResponse
    {
        $request->validate([
            'path'     => 'required|string',
            'new_name' => 'required|string|max:150',
        ]);

        $path = $request->input('path');
        $newName = $request->input('new_name');

        if (!File::exists($path) || !$this->isSafePath($path)) {
            return response()->json(['success' => false, 'message' => 'ফাইলটি খুঁজে পাওয়া যায়নি বা পাথ অবৈধ।'], 404);
        }

        $dir = dirname($path);
        $origExt = pathinfo($path, PATHINFO_EXTENSION);
        $cleanBase = Str::slug(pathinfo($newName, PATHINFO_FILENAME));

        if (!$cleanBase) {
            return response()->json(['success' => false, 'message' => 'অবৈধ ফাইলের নাম।'], 422);
        }

        $newPath = $dir . '/' . $cleanBase . '.' . $origExt;

        if (File::exists($newPath) && $newPath !== $path) {
            $newPath = $dir . '/' . $cleanBase . '_' . substr(uniqid(), -4) . '.' . $origExt;
        }

        File::move($path, $newPath);

        if ($this->accessService) {
            $this->accessService->log('rename_media', "ফাইলের নাম পরিবর্তন: " . basename($path) . " -> " . basename($newPath));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'ফাইলের নাম সফলভাবে পরিবর্তন করা হয়েছে!',
            'new_name' => basename($newPath),
            'new_path' => $newPath,
        ]);
    }

    /**
     * Bulk action: Delete, Move, or Optimize selected assets.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action'        => 'required|string|in:delete,move,optimize,convert_webp',
            'paths'         => 'required|array|min:1',
            'paths.*'       => 'required|string',
            'target_folder' => 'nullable|string',
        ]);

        $action = $request->input('action');
        $paths = $request->input('paths', []);
        $targetFolder = $request->input('target_folder');
        $folderDefs = $this->getFolderDefinitions();

        $processedCount = 0;
        $totalBytesSaved = 0;

        foreach ($paths as $path) {
            if (!File::exists($path) || !$this->isSafePath($path)) {
                continue;
            }

            if ($action === 'delete') {
                File::delete($path);
                $processedCount++;
            } elseif ($action === 'move' && $targetFolder && isset($folderDefs[$targetFolder])) {
                $targetDir = $folderDefs[$targetFolder]['default_upload'];
                if (!File::isDirectory($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true, true);
                }
                $destination = $targetDir . '/' . basename($path);
                if ($destination !== $path) {
                    File::move($path, $destination);
                    $processedCount++;
                }
            } elseif ($action === 'optimize') {
                $saved = $this->optimizeImageFile($path);
                $processedCount++;
                $totalBytesSaved += $saved;
            } elseif ($action === 'convert_webp') {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'avif', 'bmp'])) {
                    $webpRes = \App\Services\ImageOptimizerService::convertImageToWebp($path, 85, true);
                    if ($webpRes['success']) {
                        $processedCount++;
                        $totalBytesSaved += max(0, $webpRes['bytes_saved']);
                    }
                }
            }
        }

        $msg = match ($action) {
            'delete'       => "নির্বাচিত {$processedCount}টি ফাইল সফলভাবে মুছে ফেলা হয়েছে!",
            'move'         => "নির্বাচিত {$processedCount}টি ফাইল '{$folderDefs[$targetFolder]['label']}' ফোল্ডারে সরানো হয়েছে!",
            'optimize'     => "নির্বাচিত {$processedCount}টি ফাইল অপ্টিমাইজ সম্পন্ন হয়েছে! (" . $this->formatBytes($totalBytesSaved) . " সাশ্রয়)",
            'convert_webp' => "নির্বাচিত {$processedCount}টি ফাইল আধুনিক WebP ফরম্যাটে রূপান্তর সম্পন্ন হয়েছে! (" . $this->formatBytes($totalBytesSaved) . " সাশ্রয়)",
        };

        if ($this->accessService) {
            $this->accessService->log('bulk_media_' . $action, $msg);
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'count'   => $processedCount,
        ]);
    }

    /**
     * Replace an existing media asset in-place without changing its file link/URL.
     */
    public function replaceFile(Request $request): JsonResponse
    {
        $request->validate([
            'target_path' => 'required|string',
            'file'        => 'required|image|mimes:jpeg,png,jpg,webp,svg,gif,ico,bmp,avif|max:10240',
        ]);

        $targetPath = $request->input('target_path');
        $uploadedFile = $request->file('file');

        if (!File::exists($targetPath) || !$this->isSafePath($targetPath)) {
            return response()->json(['success' => false, 'message' => 'টার্গেট ফাইল খুঁজে পাওয়া যায়নি বা পাথ অবৈধ!'], 404);
        }

        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return response()->json(['success' => false, 'message' => 'অবৈধ আপলোড ফাইল!'], 422);
        }

        $dir = dirname($targetPath);
        $filename = basename($targetPath);
        $targetExt = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $newExt = strtolower($uploadedFile->getClientOriginalExtension());

        // Backup existing file temporarily
        $tempBackup = $targetPath . '.bak';
        @File::copy($targetPath, $tempBackup);

        try {
            // If target is webp and uploaded is png/jpg, convert uploaded to webp directly into targetPath
            if ($targetExt === 'webp' && in_array($newExt, ['jpg', 'jpeg', 'png', 'avif', 'bmp'])) {
                $tempUpload = $dir . '/temp_' . uniqid() . '.' . $newExt;
                $uploadedFile->move($dir, basename($tempUpload));
                $res = \App\Services\ImageOptimizerService::convertImageToWebp($tempUpload, 85, true);
                if ($res['success'] && File::exists($res['webp_path'])) {
                    File::move($res['webp_path'], $targetPath);
                } else {
                    $uploadedFile->move($dir, $filename);
                }
            } else {
                $uploadedFile->move($dir, $filename);
            }

            // Optimize in-place
            $this->optimizeImageFile($targetPath);
            @File::delete($tempBackup);

            // Clear cached dimension
            $mtime = File::lastModified($targetPath);
            \Illuminate\Support\Facades\Cache::forget('media_dim_' . md5($targetPath) . '_' . $mtime);

            if ($this->accessService) {
                $this->accessService->log('replace_media', "মিডিয়া অ্যাসেট '{$filename}' সফলভাবে রিপ্লেস করা হয়েছে");
            }

            return response()->json([
                'success' => true,
                'message' => "অ্যাসেট '{$filename}' সফলভাবে নতুন ছবি দিয়ে রিপ্লেস ও অপ্টিমাইজ করা হয়েছে!",
            ]);
        } catch (\Throwable $e) {
            if (File::exists($tempBackup)) {
                @File::move($tempBackup, $targetPath);
            }
            return response()->json(['success' => false, 'message' => 'ফাইল রিপ্লেস ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new folder directory.
     */
    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'folder_name' => 'required|string|max:50',
            'location'    => 'required|string|in:storage,public',
        ]);

        $name = Str::slug($request->input('folder_name'));
        $loc = $request->input('location');

        $baseDir = ($loc === 'public') ? public_path('images/' . $name) : storage_path('app/public/' . $name);

        if (File::isDirectory($baseDir)) {
            return response()->json(['success' => false, 'message' => 'এই নামের ফোল্ডার ইতোমধ্যে বিদ্যমান রয়েছে।'], 422);
        }

        File::makeDirectory($baseDir, 0755, true, true);

        if ($this->accessService) {
            $this->accessService->log('create_media_folder', "নতুন ফোল্ডার তৈরি করা হয়েছে: {$name}");
        }

        return response()->json([
            'success' => true,
            'message' => "নতুন ফোল্ডার '{$name}' সফলভাবে তৈরি হয়েছে!",
        ]);
    }

    /**
     * Download selected files as a ZIP archive.
     */
    public function downloadZip(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'paths' => 'required|array|min:1',
        ]);

        if (!class_exists('ZipArchive')) {
            return response()->json(['success' => false, 'message' => 'সার্ভারে ZipArchive এক্সটেনশন সক্রিয় নেই।'], 500);
        }

        $paths = $request->input('paths', []);
        $zipName = 'ideaabd_media_assets_' . date('Ymd_His') . '.zip';
        $tempZipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['success' => false, 'message' => 'ZIP ফাইল তৈরি ব্যর্থ হয়েছে।'], 500);
        }

        $addedCount = 0;
        foreach ($paths as $p) {
            if (File::exists($p) && $this->isSafePath($p)) {
                $zip->addFile($p, basename($p));
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0 || !File::exists($tempZipPath)) {
            return response()->json(['success' => false, 'message' => 'কোনো বৈধ ফাইল জিপে যুক্ত করা যায়নি।'], 422);
        }

        return response()->download($tempZipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Auto-Optimize All Existing Images in Library.
     */
    public function optimizeAll(Request $request): JsonResponse|RedirectResponse
    {
        $folderDefs = $this->getFolderDefinitions();
        $optimizedCount = 0;
        $totalBytesSaved = 0;

        foreach ($folderDefs as $fConfig) {
            foreach ($fConfig['dirs'] as $dir) {
                if (!File::isDirectory($dir)) {
                    continue;
                }

                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $saved = $this->optimizeImageFile($file->getPathname());
                        if ($saved > 0) {
                            $optimizedCount++;
                            $totalBytesSaved += $saved;
                        }
                    }
                }
            }
        }

        $formattedSaved = $this->formatBytes($totalBytesSaved);

        if ($this->accessService) {
            $this->accessService->log('optimize_media', "মিডিয়া লাইব্রেরির {$optimizedCount}টি ছবি অপ্টিমাইজ করা হয়েছে (সাশ্রয়: {$formattedSaved})");
        }

        $msg = $optimizedCount > 0
            ? "মোট {$optimizedCount}টি ছবি অপ্টিমাইজ সম্পন্ন হয়েছে! সর্বমোট {$formattedSaved} স্টোরেজ সাশ্রয় হয়েছে।"
            : "সকল ছবি ইতোমধ্যে সর্বোচ্চ অপ্টিমাইজড অবস্থায় রয়েছে।";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'count'   => $optimizedCount,
                'saved'   => $formattedSaved,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * 1-Click Convert All Existing PNG / JPG Images Across the Application to Modern WebP.
     */
    public function convertAllToWebp(Request $request): JsonResponse
    {
        $deleteOriginal = $request->boolean('delete_original', true);
        $folder = $request->input('folder', 'all');
        $quality = (int) $request->input('quality', 85);
        if ($quality < 50 || $quality > 100) $quality = 85;

        $folderDefs = $this->getFolderDefinitions();

        $dirsToScan = [];
        if ($folder !== 'all' && isset($folderDefs[$folder])) {
            $dirsToScan = $folderDefs[$folder]['dirs'];
        } else {
            $dirsToScan = [
                storage_path('app/public'),
                public_path('images'),
            ];
        }

        $totalConverted = 0;
        $totalBytesSaved = 0;
        $convertedList = [];

        foreach ($dirsToScan as $dir) {
            if (is_dir($dir)) {
                $res = \App\Services\ImageOptimizerService::batchConvertDirectoryToWebp($dir, $quality, $deleteOriginal);
                $totalConverted += $res['converted_count'];
                $totalBytesSaved += $res['bytes_saved'];
                if (!empty($res['converted_files'])) {
                    $convertedList = array_merge($convertedList, $res['converted_files']);
                }
            }
        }

        $formattedSaved = $this->formatBytes($totalBytesSaved);

        if ($this->accessService) {
            $this->accessService->log('convert_all_webp', "মিডিয়া লাইব্রেরির {$totalConverted}টি ছবিকে WebP ফরম্যাটে রূপান্তর করা হয়েছে (সাশ্রয়: {$formattedSaved})");
        }

        $msg = $totalConverted > 0
            ? "মোট {$totalConverted}টি PNG/JPG ফাইল সফলভাবে আধুনিক WebP ফরম্যাটে রূপান্তর করা হয়েছে! সর্বমোট {$formattedSaved} স্টোরেজ সাশ্রয় হয়েছে।"
            : "নির্বাচিত ফোল্ডারের সকল ছবি ইতোমধ্যে আধুনিক WebP ফরম্যাটে রূপান্তর ও অপ্টিমাইজড অবস্থায় রয়েছে।";

        return response()->json([
            'success' => true,
            'message' => $msg,
            'count'   => $totalConverted,
            'saved'   => $formattedSaved,
            'items'   => array_slice($convertedList, 0, 50),
        ]);
    }

    /**
     * Optimize a single image file in place.
     * Returns the number of bytes saved, or 0 if unchanged.
     */
    private function optimizeImageFile(string $filePath, int $maxWidth = 1920, bool $convertToWebp = false): int
    {
        if (!File::exists($filePath) || !extension_loaded('gd')) {
            return 0;
        }

        $origSize = File::size($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            return 0;
        }

        try {
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                return 0;
            }

            $origWidth = $imageInfo[0];
            $origHeight = $imageInfo[1];
            $maxHeight = $maxWidth;

            // Load source image
            $srcImage = match ($ext) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($filePath),
                'png'         => @imagecreatefrompng($filePath),
                'webp'        => @imagecreatefromwebp($filePath),
                default       => null,
            };

            if (!$srcImage) {
                return 0;
            }

            // Calculate resized dimensions if needed
            $newWidth = $origWidth;
            $newHeight = $origHeight;

            if ($maxWidth > 0 && ($origWidth > $maxWidth || $origHeight > $maxHeight)) {
                $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
                $newWidth = (int) round($origWidth * $ratio);
                $newHeight = (int) round($origHeight * $ratio);
            }

            $targetImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency
            if ($ext === 'png' || $ext === 'webp' || $convertToWebp) {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            $tempPath = $filePath . '.tmp';
            $saved = match ($ext) {
                'jpg', 'jpeg' => imagejpeg($targetImage, $tempPath, 84),
                'png'         => imagepng($targetImage, $tempPath, 8),
                'webp'        => imagewebp($targetImage, $tempPath, 82),
                default       => false,
            };

            imagedestroy($srcImage);
            imagedestroy($targetImage);

            if ($saved && File::exists($tempPath)) {
                $newSize = File::size($tempPath);
                if ($newSize < $origSize || $newWidth < $origWidth) {
                    File::move($tempPath, $filePath);
                    return max(0, $origSize - $newSize);
                }
                File::delete($tempPath);
            }
        } catch (\Throwable $e) {
            if (isset($tempPath) && File::exists($tempPath)) {
                @File::delete($tempPath);
            }
        }

        return 0;
    }

    /**
     * Delete single media asset.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $path = $request->input('path');
        if (!$path || !File::exists($path) || !$this->isSafePath($path)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'ফাইলটি খুঁজে পাওয়া যায়নি বা অননুমোদিত পাথ!'], 404);
            }
            return back()->with('error', 'ফাইলটি খুঁজে পাওয়া যায়নি বা অননুমোদিত পাথ!');
        }

        File::delete($path);

        if ($this->accessService) {
            $this->accessService->log('delete_media', "মিডিয়া লাইব্রেরি থেকে ফাইল '" . basename($path) . "' মুছে ফেলা হয়েছে");
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'মিডিয়া ফাইল সফলভাবে মুছে ফেলা হয়েছে!']);
        }

        return back()->with('success', 'মিডিয়া ফাইল সফলভাবে মুছে ফেলা হয়েছে!');
    }

    /**
     * Security check: ensure path is within public or storage.
     */
    private function isSafePath(string $path): bool
    {
        $publicDir = realpath(public_path());
        $storageDir = realpath(storage_path());
        $realPath = realpath($path);

        if (!$realPath) {
            // Path may not exist yet if writing a new file
            $parentDir = realpath(dirname($path));
            if (!$parentDir) {
                return false;
            }
            return str_starts_with($parentDir, $publicDir) || str_starts_with($parentDir, $storageDir);
        }

        return str_starts_with($realPath, $publicDir) || str_starts_with($realPath, $storageDir);
    }

    /**
     * Format bytes.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
