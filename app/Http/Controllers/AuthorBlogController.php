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
     * Public gateway for writing blog posts.
     * Directs guests to login/register, pending authors to approval status, and approved authors to write dashboard.
     */
    public function writeGateway(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            session()->put('url.intended', route('blog.write'));
            return redirect()->route('login')->with('info', 'ব্লগে নিজের লেখা প্রকাশ করতে অনুগ্রহ করে আপনার মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে লগইন করুন অথবা নতুন লেখক হিসেবে নিবন্ধন করুন।');
        }

        $user = auth()->user();

        // If user's registration is pending approval
        if ($user->reg_status === 'pending' || $user->status === 'pending') {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে। অনুমোদন সম্পন্ন হলে আপনি ব্লগে লেখা পোস্ট করতে পারবেন।');
        }

        // If user is rejected
        if ($user->reg_status === 'rejected' || $user->status === 'rejected') {
            return redirect()->route('home')->with('error', 'আপনার লেখক অ্যাকাউন্টের আবেদনটি অনুমোদিত হয়নি।');
        }

        // Auto assign author role for registered approved users
        if ($user->role === 'customer' || empty($user->role)) {
            $user->role = 'author';
            $user->reg_status = 'approved';
            $user->save();
        }

        return redirect()->route('author.dashboard', ['tab' => 'write']);
    }

    /**
     * Dedicated Author Dashboard for registered and approved writers.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        // Check if user is pending approval
        if ($user->reg_status === 'pending' || $user->status === 'pending') {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে।');
        }

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
            'category_id'    => 'required|integer|exists:blog_categories,id',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'required|image|max:4096',
            'action_type'    => 'required|in:draft,submit',
        ], [
            'title.required'          => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'category_id.required'    => 'লেখার ক্যাটাগরি বা বিষয় নির্বাচন করুন।',
            'content.required'        => 'ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।',
            'featured_image.required' => 'লেখার সাথে একটি নির্ধারিত মাপের (১২০০×৬৭৫ বা ৮০০×৪৫০) ফটোকার্ড কভার ছবি নির্বাচন করা বাধ্যতামূলক।',
        ]);

        $user = auth()->user();

        // Ensure author role
        if ($user->role !== 'author' && !$user->isAdmin()) {
            $user->role = 'author';
            $user->reg_status = 'approved';
            $user->save();
        }

        $isSubmit = $validated['action_type'] === 'submit';

        // Unique automatic English slug generation
        $slug = $this->generateEnglishSlug($validated['title']);

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
            'category_id'    => 'required|integer|exists:blog_categories,id',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:4096',
            'action_type'    => 'required|in:draft,submit',
        ], [
            'title.required'       => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'category_id.required' => 'লেখার ক্যাটাগরি বা বিষয় নির্বাচন করুন।',
            'content.required'     => 'ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।',
        ]);

        $isSubmit = $validated['action_type'] === 'submit';

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }

        $post->title = $validated['title'];
        // Update slug to clean english if empty or modified
        if (empty($post->slug) || !preg_match('/^[a-z0-9-]+$/i', $post->slug)) {
            $post->slug = $this->generateEnglishSlug($validated['title'], $post->id);
        }
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

    /**
     * Generate automatic unique English slug from Bengali/English title.
     */
    public function generateEnglishSlug(string $title, ?int $ignoreId = null): string
    {
        $bengali = [
            'অ'=>'a','আ'=>'aa','ই'=>'i','ঈ'=>'ee','উ'=>'u','ঊ'=>'oo','ঋ'=>'ri',
            'এ'=>'e','ঐ'=>'oi','ও'=>'o','ঔ'=>'ou',
            'ক'=>'k','খ'=>'kh','গ'=>'g','ঘ'=>'gh','ঙ'=>'ng',
            'চ'=>'ch','ছ'=>'chh','জ'=>'j','ঝ'=>'jh','ঞ'=>'ny',
            'ট'=>'t','ঠ'=>'th','ড'=>'d','ঢ'=>'dh','ণ'=>'n',
            'ত'=>'t','থ'=>'th','দ'=>'d','ধ'=>'dh','ন'=>'n',
            'প'=>'p','ফ'=>'f','ব'=>'b','ভ'=>'bh','ম'=>'m',
            'য'=>'z','র'=>'r','ল'=>'l','শ'=>'sh','ষ'=>'sh','স'=>'s','হ'=>'h',
            'ড়'=>'r','ঢ়'=>'rh','য়'=>'y','ৎ'=>'t','ং'=>'ng','ঃ'=>'h','ঁ'=>'n',
            'া'=>'a','ি'=>'i','ী'=>'ee','ু'=>'u','ূ'=>'oo','ৃ'=>'ri','ে'=>'e','ৈ'=>'oi','ো'=>'o','ৌ'=>'ou','্'=>''
        ];

        $transliterated = strtr($title, $bengali);
        $clean = preg_replace('/[^a-zA-Z0-9\s-]/', '', $transliterated);
        $slugBase = Str::slug($clean, '-');

        if (empty($slugBase) || strlen($slugBase) < 3) {
            $slugBase = 'post-' . Str::lower(Str::random(6));
        }

        $slug = $slugBase;
        $counter = 1;
        $query = BlogPost::withTrashed();
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ((clone $query)->where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . (++$counter);
        }

        return $slug;
    }
}
