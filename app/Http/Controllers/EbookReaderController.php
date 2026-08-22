<?php

namespace App\Http\Controllers;

use App\Models\UserEbookLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Ebook\Models\Ebook;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EbookReaderController extends Controller
{
    /**
     * Display Full DRM Web Reader for verified buyers, author or admin.
     */
    public function read(string $slug): View|\Illuminate\Http\RedirectResponse
    {
        $ebook = Ebook::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int)$slug : 0)
            ->firstOrFail();

        $user = auth()->user();

        // Check if user has access to full e-book
        $hasAccess = false;
        $libraryEntry = null;

        if ($user) {
            if ($user->isAdmin() || $user->isSubAdmin()) {
                $hasAccess = true;
            } elseif ($ebook->author_user_id === $user->id) {
                $hasAccess = true;
            } else {
                $libraryEntry = UserEbookLibrary::where('user_id', $user->id)
                    ->where('ebook_id', $ebook->id)
                    ->where('is_active', true)
                    ->first();
                $hasAccess = (bool) $libraryEntry;
            }
        }

        // Free e-books are accessible to logged-in users
        if (!$hasAccess && (float)$ebook->price <= 0 && $user) {
            $libraryEntry = UserEbookLibrary::firstOrCreate(
                ['user_id' => $user->id, 'ebook_id' => $ebook->id],
                ['access_type' => 'free', 'is_active' => true]
            );
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return redirect()->route('ebook.preview', $ebook->slug ?: $ebook->id)
                ->with('info', 'সম্পূর্ণ বইটি পড়ার জন্য অনুগ্রহ করে প্রথমে বইটি ক্রয় করুন অথবা ফ্রি প্রিভিউ পড়ুন।');
        }

        // Increment read count
        $ebook->increment('read_count');

        // Dynamic anti-piracy watermark text
        $watermarkText = ($user ? ($user->name . ' (' . ($user->phone ?: $user->email) . ')') : 'আইডিয়া প্রকাশন') 
            . ' • ' . ($libraryEntry ? ('Order #' . $libraryEntry->order_id) : 'Licensed Reader') 
            . ' • ' . date('d-m-Y');

        return view('frontend.ebooks.reader', compact('ebook', 'libraryEntry', 'watermarkText'));
    }

    /**
     * Display Free Sample Preview Reader (First 10-15 pages / Sample File).
     */
    public function preview(string $slug): View
    {
        $ebook = Ebook::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int)$slug : 0)
            ->firstOrFail();

        $watermarkText = 'ফ্রি নমুনা অংশ (Sample Preview) • আইডিয়া প্রকাশন • সর্বস্বত্ব সংরক্ষিত';

        return view('frontend.ebooks.preview', compact('ebook', 'watermarkText'));
    }

    /**
     * Secure PDF Stream Endpoint (Prevents raw download links).
     */
    public function streamPdf(int $id, Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        $ebook = Ebook::findOrFail($id);
        $user = auth()->user();
        $isSample = $request->query('sample') == '1';

        $filePath = $isSample ? ($ebook->sample_file_path ?: $ebook->file_path) : $ebook->file_path;

        if (!$filePath) {
            abort(404, 'ফাইল পাওয়া যায়নি।');
        }

        // Access check if not sample preview
        if (!$isSample) {
            $hasAccess = false;
            if ($user) {
                if ($user->isAdmin() || $user->isSubAdmin() || $ebook->author_user_id === $user->id) {
                    $hasAccess = true;
                } else {
                    $hasAccess = UserEbookLibrary::where('user_id', $user->id)
                        ->where('ebook_id', $ebook->id)
                        ->where('is_active', true)
                        ->exists();
                }
            }
            if (!$hasAccess && (float)$ebook->price <= 0 && $user) {
                $hasAccess = true;
            }
            if (!$hasAccess) {
                abort(403, 'Unauthorized e-book stream.');
            }
        }

        $fullPath = storage_path('app/public/' . ltrim($filePath, '/'));
        if (!file_exists($fullPath)) {
            abort(404, 'ই-বুক ফাইল সার্ভারে পাওয়া যায়নি।');
        }

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ebook_preview.pdf"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Save reading progress & bookmark.
     */
    public function saveProgress(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $validated = $request->validate([
            'last_read_page'   => 'required|integer|min:1',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'bookmark'         => 'nullable|string|max:255',
        ]);

        $entry = UserEbookLibrary::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->first();

        if ($entry) {
            $bookmarks = $entry->bookmarks_data ?? [];
            if (!empty($validated['bookmark'])) {
                $bookmarks[] = [
                    'page' => $validated['last_read_page'],
                    'note' => $validated['bookmark'],
                    'time' => now()->toIso8601String(),
                ];
            }

            $entry->update([
                'last_read_page'   => $validated['last_read_page'],
                'progress_percent' => $validated['progress_percent'] ?? $entry->progress_percent,
                'bookmarks_data'   => $bookmarks,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
