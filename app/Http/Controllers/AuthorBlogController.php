<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorBlogController extends Controller
{
    /**
     * Dedicated Author Dashboard for registered and approved writers.
     */
    public function dashboard(Request $request): View
    {
        $user = auth()->user();

        // If user is a general customer visiting author dashboard, auto assign role or approve author access
        if ($user->role === 'customer' || empty($user->role)) {
            $user->role = 'author';
            $user->reg_status = 'approved';
            $user->save();
        }

        $filterStatus = $request->input('status', 'all');

        $query = BlogPost::where('author_id', $user->id)
            ->with(['category']);

        if ($filterStatus !== 'all' && in_array($filterStatus, ['published', 'pending', 'draft', 'rejected'])) {
            $query->where('status', $filterStatus);
        }

        $posts = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total'     => BlogPost::where('author_id', $user->id)->count(),
            'published' => BlogPost::where('author_id', $user->id)->where('status', 'published')->count(),
            'pending'   => BlogPost::where('author_id', $user->id)->where('status', 'pending')->count(),
            'draft'     => BlogPost::where('author_id', $user->id)->where('status', 'draft')->count(),
            'rejected'  => BlogPost::where('author_id', $user->id)->where('status', 'rejected')->count(),
            'views'     => (int) BlogPost::where('author_id', $user->id)->sum('view_count'),
        ];

        $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();

        $editId = $request->input('edit_id') ?: $request->input('edit_post_id');
        $editPost = null;
        if ($editId) {
            $editPost = BlogPost::where('id', $editId)->where('author_id', $user->id)->first();
        }

        return view('author.dashboard', compact('user', 'stats', 'posts', 'blogCategories', 'editPost', 'filterStatus'));
    }

    /**
     * Helper redirect to write tab in dashboard.
     */
    public function createPost(): RedirectResponse
    {
        return redirect()->route('author.dashboard', ['tab' => 'write']);
    }

    /**
     * Helper redirect to edit draft post.
     */
    public function editPost(int $id): RedirectResponse
    {
        return redirect()->route('author.dashboard', ['tab' => 'write', 'edit_id' => $id]);
    }

    /**
     * Store a new blog post created by the author.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'category_id'    => 'nullable|integer|exists:blog_categories,id',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:4096',
            'action_type'    => 'required|in:draft,submit',
        ], [
            'title.required'   => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'content.required' => 'ব্লগের মূল বিষয়বস্তু বা লেখা দিন।',
        ]);

        $user = auth()->user();

        // Ensure author role
        if ($user->role !== 'author' && !$user->isAdmin()) {
            $user->role = 'author';
            $user->reg_status = 'approved';
            $user->save();
        }

        $isSubmit = $validated['action_type'] === 'submit';

        // Unique slug generation
        $slugBase = $this->bengaliToEnglish($validated['title']) ?: Str::slug(Str::random(8));
        $slug = $slugBase;
        $counter = 1;
        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . (++$counter);
        }

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog', 'public');
        }

        $post = new BlogPost();
        $post->title = $validated['title'];
        $post->slug = $slug;
        $post->category_id = $validated['category_id'] ?: null;
        $post->excerpt = $validated['excerpt'] ?: Str::limit(strip_tags($validated['content']), 200);
        $post->content = $validated['content'];
        $post->featured_image = $imagePath;
        $post->author_id = $user->id;
        $post->submitted_by = $user->id;
        
        if ($isSubmit) {
            $post->status = 'pending';
            $post->mod_status = 'pending';
            $message = 'আপনার লেখাটি সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনা করে শীঘ্রই তা ব্লগে প্রকাশ করবেন।';
        } else {
            $post->status = 'draft';
            $post->mod_status = 'pending';
            $message = 'লেখাটি খসড়া (Draft) হিসেবে সংরক্ষিত হয়েছে। যেকোনো সময় এটি এডিট করে জমা দিতে পারবেন।';
        }

        $post->save();

        return redirect()->route('author.dashboard', ['tab' => 'articles'])->with('success', $message);
    }

    /**
     * Update an existing draft or rejected blog post.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $post = BlogPost::where('id', $id)->where('author_id', $user->id)->firstOrFail();

        // Lock check: Once submitted or approved/published, author cannot edit directly
        if ($post->status === 'pending' || $post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('author.dashboard', ['tab' => 'articles'])
                ->with('error', 'পোস্টটি অনুমোদনের জন্য অপেক্ষমাণ বা ইতোমধ্যে প্রকাশিত হয়েছে। তাই এটি আর সম্পাদনা করা যাবে না।');
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'category_id'    => 'nullable|integer|exists:blog_categories,id',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:4096',
            'action_type'    => 'required|in:draft,submit',
        ]);

        $isSubmit = $validated['action_type'] === 'submit';

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }

        $post->title = $validated['title'];
        $post->category_id = $validated['category_id'] ?: null;
        $post->excerpt = $validated['excerpt'] ?: Str::limit(strip_tags($validated['content']), 200);
        $post->content = $validated['content'];

        if ($isSubmit) {
            $post->status = 'pending';
            $post->mod_status = 'pending';
            $post->rejection_reason = null;
            $message = 'লেখাটি সফলভাবে পর্যালোচনার জন্য জমা দেওয়া হয়েছে।';
        } else {
            $post->status = 'draft';
            $message = 'খসড়া লেখাটি সফলভাবে হালনাগাদ করা হয়েছে।';
        }

        $post->save();

        return redirect()->route('author.dashboard', ['tab' => 'articles'])->with('success', $message);
    }

    /**
     * Delete a draft or rejected post by the author.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $post = BlogPost::where('id', $id)->where('author_id', $user->id)->firstOrFail();

        if ($post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('author.dashboard', ['tab' => 'articles'])
                ->with('error', 'প্রকাশিত লেখা সরাসরি মোছা সম্ভব নয়। অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->forceDelete();

        return redirect()->route('author.dashboard', ['tab' => 'articles'])->with('success', 'ব্লগ পোস্টটি তালিকা থেকে মুছে ফেলা হয়েছে।');
    }

    private function bengaliToEnglish(string $text): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $text = str_replace($bengali, $english, $text);
        return Str::slug($text, '-', null);
    }
}
