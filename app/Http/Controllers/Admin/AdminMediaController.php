<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Display media library files.
     */
    public function index(Request $request): View
    {
        $folderFilter = $request->string('folder')->trim()->value() ?: 'all';
        $search = $request->string('search')->trim()->value();

        $storagePublic = storage_path('app/public');
        $publicImages = public_path('images');

        $directories = [
            'covers'   => $storagePublic . '/books/covers',
            'banners'  => $publicImages . '/banners',
            'settings' => $publicImages . '/settings',
            'qrcodes'  => $storagePublic . '/settings/qrcodes',
            'authors'  => $storagePublic . '/authors',
            'general'  => $publicImages,
        ];

        $mediaItems = [];
        $totalBytes = 0;

        foreach ($directories as $key => $dir) {
            if ($folderFilter !== 'all' && $folderFilter !== $key) {
                continue;
            }

            if (File::isDirectory($dir)) {
                $files = File::files($dir);
                foreach ($files as $file) {
                    $ext = strtolower($file->getExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'])) {
                        continue;
                    }

                    $filename = $file->getFilename();
                    if ($search && !str_contains(strtolower($filename), strtolower($search))) {
                        continue;
                    }

                    $size = $file->getSize();
                    $totalBytes += $size;

                    // Generate web URL
                    $relPath = str_replace([$storagePublic, $publicImages, public_path()], '', $file->getPathname());
                    $relPath = str_replace('\\', '/', $relPath);

                    if (str_starts_with($file->getPathname(), $storagePublic)) {
                        $url = asset('storage' . str_replace('\\', '/', str_replace($storagePublic, '', $file->getPathname())));
                    } else {
                        $url = asset(ltrim($relPath, '/'));
                    }

                    $mediaItems[] = [
                        'filename'   => $filename,
                        'folder'     => $key,
                        'path'       => $file->getPathname(),
                        'url'        => $url,
                        'size'       => $this->formatBytes($size),
                        'size_bytes' => $size,
                        'ext'        => $ext,
                        'updated_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                    ];
                }
            }
        }

        // Sort latest first
        usort($mediaItems, fn ($a, $b) => $b['updated_at']->timestamp <=> $a['updated_at']->timestamp);

        $totalFormatted = $this->formatBytes($totalBytes);
        $totalCount = count($mediaItems);

        return view('admin.media', compact('mediaItems', 'totalCount', 'totalFormatted', 'folderFilter', 'search'));
    }

    /**
     * Upload new media asset.
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file'   => 'required|image|mimes:jpeg,png,jpg,webp,svg,gif,ico|max:5120',
            'folder' => 'nullable|string|in:banners,covers,settings,qrcodes,authors,general',
        ]);

        $folder = $request->input('folder', 'general');
        $file = $request->file('file');

        if ($file && $file->isValid()) {
            $name = uniqid('media_', true) . '.' . $file->getClientOriginalExtension();

            if ($folder === 'banners' || $folder === 'settings') {
                $targetDir = public_path('images/' . $folder);
                if (!File::isDirectory($targetDir)) File::makeDirectory($targetDir, 0755, true);
                $destinationPath = $targetDir . '/' . $name;
                $file->move($targetDir, $name);
            } else {
                $relPath = $file->storeAs('settings/qrcodes', $name, 'public');
                $destinationPath = storage_path('app/public/' . $relPath);
            }

            // Auto-optimize uploaded image
            $this->optimizeImageFile($destinationPath);

            if ($this->accessService) {
                $this->accessService->log('upload_media', "মিডিয়া লাইব্রেরিতে নতুন ছবি আপলোড ও অপ্টিমাইজ করা হয়েছে");
            }

            return back()->with('success', 'ছবি সফলভাবে মিডিয়া লাইব্রেরিতে আপলোড ও অপ্টিমাইজ করা হয়েছে!');
        }

        return back()->with('error', 'ফাইল আপলোড ব্যর্থ হয়েছে।');
    }

    /**
     * Auto-Optimize All Existing Images in Library.
     */
    public function optimizeAll(Request $request)
    {
        $storagePublic = storage_path('app/public');
        $publicImages = public_path('images');

        $directories = [
            $storagePublic . '/books/covers',
            $publicImages . '/banners',
            $publicImages . '/settings',
            $storagePublic . '/settings/qrcodes',
            $storagePublic . '/authors',
            $publicImages,
        ];

        $optimizedCount = 0;
        $totalBytesSaved = 0;

        foreach ($directories as $dir) {
            if (File::isDirectory($dir)) {
                $files = File::files($dir);
                foreach ($files as $file) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $beforeSize = $file->getSize();
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
     * Optimize a single image file in place.
     * Returns the number of bytes saved, or 0 if unchanged.
     */
    private function optimizeImageFile(string $filePath): int
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
            $maxWidth = 1920;
            $maxHeight = 1920;

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

            // Calculate resized dimensions if exceeding 1920px
            $newWidth = $origWidth;
            $newHeight = $origHeight;

            if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
                $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
                $newWidth = (int) round($origWidth * $ratio);
                $newHeight = (int) round($origHeight * $ratio);
            }

            $targetImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG & WebP
            if ($ext === 'png' || $ext === 'webp') {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            // Save to a temporary file first
            $tempPath = $filePath . '.tmp';
            $saved = match ($ext) {
                'jpg', 'jpeg' => imagejpeg($targetImage, $tempPath, 83),
                'png'         => imagepng($targetImage, $tempPath, 8),
                'webp'        => imagewebp($targetImage, $tempPath, 82),
                default       => false,
            };

            imagedestroy($srcImage);
            imagedestroy($targetImage);

            if ($saved && File::exists($tempPath)) {
                $newSize = File::size($tempPath);
                // Keep the new file only if it is smaller or resized
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
     * Delete media asset.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $path = $request->input('path');
        if (!$path || !File::exists($path)) {
            return back()->with('error', 'ফাইলটি খুঁজে পাওয়া যায়নি।');
        }

        // Security check: ensure path is within public or storage
        $publicDir = realpath(public_path());
        $storageDir = realpath(storage_path());
        $realPath = realpath($path);

        if (!$realPath || (!str_starts_with($realPath, $publicDir) && !str_starts_with($realPath, $storageDir))) {
            return back()->with('error', 'অননুমোদিত ফাইল মোছার চেষ্টা!');
        }

        File::delete($realPath);

        if ($this->accessService) {
            $this->accessService->log('delete_media', "মিডিয়া লাইব্রেরি থেকে ফাইল '" . basename($realPath) . "' মুছে ফেলা হয়েছে");
        }

        return back()->with('success', 'মিডিয়া ফাইল সফলভাবে মুছে ফেলা হয়েছে!');
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
