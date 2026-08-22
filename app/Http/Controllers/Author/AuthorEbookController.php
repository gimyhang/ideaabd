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
            'preview_page_limit'    => 'nullable|integer|min:1|max:50',
            'cover_image'           => 'required|image|mimes:jpeg,png,jpg,webp|max:8192',
            'file_path'             => 'required|file|mimes:pdf,epub|max:102400',
            'epub_file_path'        => 'nullable|file|mimes:epub|max:102400',
            'sample_file_path'      => 'nullable|file|mimes:pdf,epub|max:51200',
            'is_preorder'           => 'nullable|boolean',
            'preorder_release_date' => 'nullable|required_if:is_preorder,1|date|after:today',
        ]);

        // Upload Cover
        $coverPath = $request->file('cover_image')->store('ebooks/covers', 'public');

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

        // Upload Optional Sample
        $samplePath = null;
        if ($request->hasFile('sample_file_path')) {
            $samplePath = $request->file('sample_file_path')->store('ebooks/samples', 'public');
        }

        // Generate Slug
        $slug = Str::slug($validated['title']) ?: 'ebook-' . time();
        if (Ebook::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        $ebook = Ebook::create([
            'author_user_id'        => $user->id,
            'author_id'             => $author?->id,
            'author_name'           => $author?->name ?: $user->name,
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
            'pages'                 => $validated['pages'] ?? null,
            'preview_page_limit'    => $validated['preview_page_limit'] ?? 15,
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
            ->with('success', 'ই-বুকটি সফলভাবে জমা হয়েছে! অ্যাডমিন প্যানেল থেকে পর্যালোচনা ও এপ্রুভালের পর এটি লাইভ স্টোরে প্রকাশিত হবে।');
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
            'file_path'             => 'nullable|file|mimes:pdf,epub|max:102400',
            'epub_file_path'        => 'nullable|file|mimes:epub|max:102400',
            'sample_file_path'      => 'nullable|file|mimes:pdf,epub|max:51200',
            'is_preorder'           => 'nullable|boolean',
            'preorder_release_date' => 'nullable|required_if:is_preorder,1|date',
        ]);

        $data = [
            'category_id'           => $validated['category_id'],
            'publisher_id'          => $validated['publisher_id'] ?? null,
            'title'                 => $validated['title'],
            'subtitle'              => $validated['subtitle'] ?? null,
            'isbn'                  => $validated['isbn'] ?? null,
            'description'           => $validated['description'],
            'price'                 => $validated['price'],
            'discount_price'        => $validated['discount_price'] ?? null,
            'pages'                 => $validated['pages'] ?? null,
            'preview_page_limit'    => $validated['preview_page_limit'] ?? 15,
            'is_preorder'           => !empty($validated['is_preorder']),
            'preorder_release_date' => $validated['preorder_release_date'] ?? null,
            'mod_status'            => 'pending', // Re-submits for moderation upon major edit
        ];

        if ($request->hasFile('cover_image')) {
            if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('ebooks/covers', 'public');
        }

        if ($request->hasFile('file_path')) {
            $mainFile = $request->file('file_path');
            $data['file_path'] = $mainFile->store('ebooks/files', 'public');
            $data['file_type'] = strtolower($mainFile->getClientOriginalExtension());
            $data['file_size'] = $mainFile->getSize();
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
}
