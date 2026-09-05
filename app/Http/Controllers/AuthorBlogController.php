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

        // If user is a customer/buyer, prompt them to apply as an author
        if ($user->role === 'customer' || $user->role === 'buyer' || empty($user->role)) {
            return redirect()->route('register.type', 'author')->with('info', 'ব্লগে নিজের লেখা প্রকাশ করতে অনুগ্রহ করে লেখক হিসেবে রেজিস্ট্রেশন আবেদন জমা দিন। অ্যাডমিন অনুমোদনের পর আপনি লেখা প্রকাশ করতে পারবেন।');
        }

        if (!$user->isAuthor() && !$user->isAdmin()) {
            return redirect()->route('home')->with('warning', 'শুধুমাত্র অনুমোদিত লেখকগণ ব্লগে লিখতে পারেন।');
        }

        return redirect()->route('author.posts.create');
    }

    /**
     * Dedicated Author Ideapatra Posts List View.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->reg_status === 'pending' || $user->status === 'pending') {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে।');
        }

        if ($user->reg_status === 'rejected' || $user->status === 'rejected') {
            return redirect()->route('home')->with('error', 'আপনার লেখক অ্যাকাউন্টের আবেদনটি অনুমোদিত হয়নি।');
        }

        if ($user->role === 'customer' || $user->role === 'buyer' || empty($user->role)) {
            return redirect()->route('register.type', 'author')->with('info', 'লেখক ড্যাশবোর্ড ব্যবহারের জন্য লেখক হিসেবে রেজিস্ট্রেশন আবেদন করুন।');
        }

        if (!$user->isAuthor() && !$user->isAdmin()) {
            return redirect()->route('home')->with('warning', 'শুধুমাত্র অনুমোদিত লেখকগণ ড্যাশবোর্ড ব্যবহার করতে পারেন।');
        }

        $author = $user->getAuthorRecord();
        $filterStatus = $request->input('status', 'all');

        $query = BlogPost::where(function ($q) use ($user, $author) {
            $q->where('submitted_by', $user->id)
              ->orWhere('author_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->with(['category']);

        if ($filterStatus !== 'all' && in_array($filterStatus, ['published', 'pending', 'draft', 'rejected'])) {
            $query->where('status', $filterStatus);
        }

        $posts = $query->latest('id')->paginate(12)->withQueryString();

        $basePostsQuery = BlogPost::where(function ($q) use ($user, $author) {
            $q->where('submitted_by', $user->id)
              ->orWhere('author_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        });

        $stats = [
            'total'     => (clone $basePostsQuery)->count(),
            'published' => (clone $basePostsQuery)->where('status', 'published')->count(),
            'pending'   => (clone $basePostsQuery)->where('status', 'pending')->count(),
            'draft'     => (clone $basePostsQuery)->where('status', 'draft')->count(),
            'rejected'  => (clone $basePostsQuery)->where('status', 'rejected')->count(),
            'views'     => (int) (clone $basePostsQuery)->sum('view_count'),
        ];

        return view('author.posts.index', compact('user', 'author', 'stats', 'posts', 'filterStatus'));
    }

    /**
     * Dedicated Author Dashboard (alias to index or dashboard).
     */
    public function dashboard(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Dedicated Author Ideapatra Write View.
     */
    public function createPost(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি।');
        }

        $author = $user->getAuthorRecord();
        $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        if ($blogCategories->isEmpty()) {
            $defaultCategories = ['সাহিত্য ও সংস্কৃতি', 'প্রবন্ধ ও গবেষণা', 'বই পর্যালোচনা ও সমালোচনা', 'কবিতা ও গল্প', 'মতামত ও দর্শন'];
            foreach ($defaultCategories as $catName) {
                BlogCategory::firstOrCreate(
                    ['name' => $catName],
                    ['is_active' => true, 'slug' => 'cat-' . Str::lower(Str::random(6))]
                );
            }
            $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        }

        return view('author.posts.create', compact('user', 'author', 'blogCategories'));
    }

    /**
     * Dedicated Author Ideapatra Edit View for Draft / Rejected posts.
     */
    public function editPost(int $id): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি।');
        }

        $author = $user->getAuthorRecord();
        $post = BlogPost::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('submitted_by', $user->id)
                  ->orWhere('author_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        // If post is published or approved, open the dedicated edit request / correction form
        if ($post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('author.posts.edit-request', $id);
        }

        if ($post->status === 'pending') {
            return redirect()->route('author.posts.index')
                ->with('warning', 'পোস্টটি অ্যাডমিন অনুমোদনের জন্য অপেক্ষমাণ রয়েছে।');
        }

        $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();

        return view('author.posts.edit', compact('user', 'author', 'post', 'blogCategories'));
    }

    /**
     * Dedicated Author Ideapatra Edit Request View for published posts.
     */
    public function editRequestForm(int $id): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি।');
        }

        $author = $user->getAuthorRecord();
        $post = BlogPost::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('submitted_by', $user->id)
                  ->orWhere('author_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();

        return view('author.posts.edit_request', compact('user', 'author', 'post', 'blogCategories'));
    }

    /**
     * Submit an edit request / correction for a published post.
     */
    public function submitEditRequest(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি।');
        }

        $author = $user->getAuthorRecord();
        $post = BlogPost::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('submitted_by', $user->id)
                  ->orWhere('author_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        $rules = [
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:500',
            'category_id'    => 'nullable|integer',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:8192',
            'notes'          => 'nullable|string|max:1000',
        ];

        $validated = $request->validate($rules, [
            'title.required'   => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'content.required' => 'ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।',
            'featured_image.image' => 'ছবি ফাইলটি একটি বৈধ ইমেজ হতে হবে।',
        ]);

        $categoryId = $request->input('category_id') ?: $post->category_id;
        $categoryName = BlogCategory::where('id', $categoryId)->value('name') ?? ($post->category?->name ?? 'General');

        $imagePath = $post->featured_image;
        if ($request->hasFile('featured_image')) {
            try {
                $imagePath = \App\Services\ImageOptimizerService::convertAndStore($request->file('featured_image'), 'blog', 'public');
            } catch (\Throwable $e) {}
        } elseif ($request->filled('ai_photocard_data')) {
            try {
                $imagePath = \App\Services\ImageOptimizerService::convertBase64AndStore($request->input('ai_photocard_data'), 'blog', 'public');
            } catch (\Throwable $e) {}
        }

        $editData = [
            'title'          => trim($validated['title']),
            'subtitle'       => $validated['subtitle'] ? trim($validated['subtitle']) : null,
            'category_id'    => $categoryId,
            'category_name'  => $categoryName,
            'excerpt'        => $validated['excerpt'] ? trim($validated['excerpt']) : Str::limit(strip_tags($validated['content']), 200),
            'content'        => $validated['content'],
            'featured_image' => $imagePath,
        ];

        $post->edit_request_status = 'pending';
        $post->edit_request_data = $editData;
        $post->edit_requested_at = now();
        $post->edit_request_notes = $request->input('notes') ?: 'বানান ও তথ্য সংশোধন';
        $post->edit_request_rejection_reason = null;
        $post->save();

        return redirect()->route('author.posts.index')
            ->with('success', "‘{$post->title}’ পোস্টটির সংশোধনী আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনা করে অনুমোদন দিলে সংশোধিত লেখাটি মূল লেখার সাথে স্বয়ংক্রিয়ভাবে রিপ্লেস হয়ে যাবে।");
    }

    /**
     * Store a new blog post created by the author.
     */
    public function store(Request $request): RedirectResponse
    {
        $hasAiImage = !empty($request->input('ai_photocard_data'));
        $hasUpload = $request->hasFile('featured_image');

        $rules = [
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:500',
            'category_id'    => 'nullable|integer',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:8192',
            'action_type'    => 'required|in:draft,submit',
        ];

        if ($request->input('action_type') === 'submit') {
            $rules['agree_policy'] = 'accepted';
        }

        $validated = $request->validate($rules, [
            'title.required'        => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'content.required'      => 'ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।',
            'featured_image.image'  => 'ফটোকার্ডটি একটি বৈধ ছবি (JPG, PNG, WebP) হতে হবে।',
            'featured_image.max'    => 'ছবি ফাইলের সর্বোচ্চ সাইজ ৮ মেগাবাইট হতে পারবে।',
            'agree_policy.accepted' => 'লেখা জমা দেওয়ার পূর্বে প্রকাশনার শর্তাবলি ও সম্পাদকীয় নীতিমালায় সম্মতি প্রদান করুন।',
        ]);

        $user = auth()->user();

        // Ensure author role and approval
        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি। অনুমোদন সম্পন্ন হলে আপনি লেখা পোস্ট করতে পারবেন।');
        }

        $isSubmit = ($validated['action_type'] ?? 'submit') === 'submit';

        // Unique automatic English slug generation
        $slug = $this->generateEnglishSlug($validated['title']);

        // Resilient Category assignment
        $categoryId = $request->input('category_id');
        if (!$categoryId || !BlogCategory::where('id', $categoryId)->exists()) {
            $firstCat = BlogCategory::where('is_active', true)->first() ?: BlogCategory::firstOrCreate(
                ['name' => 'সাহিত্য ও সংস্কৃতি'],
                ['is_active' => true, 'slug' => 'literature-culture']
            );
            $categoryId = $firstCat->id;
        }

        // Handle Image: Uploaded File takes precedence, then Base64 AI photocard, then Auto-generated title card
        $imagePath = null;
        try {
            if ($hasUpload) {
                $imagePath = \App\Services\ImageOptimizerService::convertAndStore($request->file('featured_image'), 'blog', 'public');
            } elseif ($hasAiImage) {
                $imagePath = \App\Services\ImageOptimizerService::convertBase64AndStore($request->input('ai_photocard_data'), 'blog', 'public');
            } else {
                $imagePath = $this->generateDefaultPhotocard($validated['title'], $user->name);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Featured image upload/generation error: " . $e->getMessage());
            $imagePath = $this->generateDefaultPhotocard($validated['title'], $user->name);
        }

        if (empty($imagePath)) {
            $imagePath = $this->generateDefaultPhotocard($validated['title'], $user->name);
        }

        // Title unique check handling
        $title = trim($validated['title']);
        if (BlogPost::where('title', $title)->exists()) {
            $title = $title . ' (' . Str::random(4) . ')';
        }

        try {
            $post = new BlogPost();
            $post->title = $title;
            
            // Safe subtitle assignment with auto-schema healing
            if (!empty($validated['subtitle'])) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                    try {
                        \Illuminate\Support\Facades\Schema::table('blog_posts', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->string('subtitle', 500)->nullable()->after('title');
                        });
                    } catch (\Throwable $e) {}
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                    $post->subtitle = $validated['subtitle'];
                }
            }
            
            $post->slug = $slug;
            $post->category_id = $categoryId;
            $post->excerpt = $validated['excerpt'] ?: Str::limit(strip_tags($validated['content']), 200);
            $post->content = $validated['content'];
            $post->featured_image = $imagePath;
            $post->author_id = $user->id;
            $post->submitted_by = $user->id;
            $post->owner_name = $user->name;
            $post->owner_phone = $user->phone ?: '';
            
            if ($isSubmit) {
                $post->status = 'pending';
                $post->mod_status = 'pending';
                $message = 'আপনার পোস্টটি সফলভাবে সাবমিট হয়েছে! অ্যাডমিন পর্যালোচনা করে শীঘ্রই তা ব্লগে প্রকাশ করবেন।';
            } else {
                $post->status = 'draft';
                $post->mod_status = 'pending';
                $message = 'আপনার লেখাটি খসড়া (Draft) হিসেবে সফলভাবে সংরক্ষিত হয়েছে।';
            }

            $post->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AuthorBlogController store error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return back()->withInput()->with('error', 'পোস্টটি সংরক্ষণের সময় ত্রুটি ঘটেছে: ' . $e->getMessage());
        }

        return redirect()->route('author.posts.index')->with('success', $message);
    }

    /**
     * Update an existing draft or rejected blog post.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isAdmin() && (!$user->isAuthor() || $user->reg_status !== 'approved' || !$user->is_active)) {
            return redirect()->route('pending.approval')->with('warning', 'আপনার লেখক অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি।');
        }
        $author = $user->getAuthorRecord();
        $post = BlogPost::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('submitted_by', $user->id)
                  ->orWhere('author_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        // Lock check: Once submitted or approved/published, author cannot edit directly
        if ($post->status === 'pending' || $post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('author.posts.index')
                ->with('error', 'পোস্টটি অনুমোদনের জন্য অপেক্ষমাণ বা ইতোমধ্যে প্রকাশিত হয়েছে। তাই এটি আর সম্পাদনা করা যাবে না।');
        }

        $hasAiImage = !empty($request->input('ai_photocard_data'));
        $hasUpload = $request->hasFile('featured_image');

        $rules = [
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:500',
            'category_id'    => 'nullable|integer',
            'excerpt'        => 'nullable|string|max:1000',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|max:8192',
            'action_type'    => 'required|in:draft,submit',
        ];

        if ($request->input('action_type') === 'submit') {
            $rules['agree_policy'] = 'accepted';
        }

        $validated = $request->validate($rules, [
            'title.required'        => 'ব্লগ পোস্টের শিরোনাম দিন।',
            'content.required'      => 'ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।',
            'featured_image.image'  => 'ফটোকার্ডটি একটি বৈধ ছবি (JPG, PNG, WebP) হতে হবে।',
            'featured_image.max'    => 'ছবি ফাইলের সর্বোচ্চ সাইজ ৮ মেগাবাইট হতে পারবে।',
            'agree_policy.accepted' => 'লেখা জমা দেওয়ার পূর্বে প্রকাশনার শর্তাবলি ও সম্পাদকীয় নীতিমালায় সম্মতি প্রদান করুন।',
        ]);

        $isSubmit = ($validated['action_type'] ?? 'submit') === 'submit';

        // Resilient Category assignment
        $categoryId = $request->input('category_id');
        if (!$categoryId || !BlogCategory::where('id', $categoryId)->exists()) {
            $firstCat = BlogCategory::where('is_active', true)->first() ?: BlogCategory::firstOrCreate(
                ['name' => 'সাহিত্য ও সংস্কৃতি'],
                ['is_active' => true, 'slug' => 'literature-culture']
            );
            $categoryId = $firstCat->id;
        }

        try {
            if ($hasUpload) {
                if ($post->featured_image) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $post->featured_image = \App\Services\ImageOptimizerService::convertAndStore($request->file('featured_image'), 'blog', 'public');
            } elseif ($hasAiImage) {
                if ($post->featured_image) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $post->featured_image = \App\Services\ImageOptimizerService::convertBase64AndStore($request->input('ai_photocard_data'), 'blog', 'public');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Featured image update error: " . $e->getMessage());
            return back()->withInput()->with('error', 'ছবি প্রক্রিয়াকরণে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
        }

        $title = trim($validated['title']);
        if (BlogPost::where('title', $title)->where('id', '!=', $post->id)->exists()) {
            $title = $title . ' (' . Str::random(4) . ')';
        }

        $post->title = $title;
        
        // Safe subtitle assignment with auto-schema healing
        if (isset($validated['subtitle'])) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                try {
                    \Illuminate\Support\Facades\Schema::table('blog_posts', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('subtitle', 500)->nullable()->after('title');
                    });
                } catch (\Throwable $e) {}
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                $post->subtitle = $validated['subtitle'] ?: null;
            }
        }

        if (empty($post->slug) || !preg_match('/^[a-z0-9-]+$/i', $post->slug)) {
            $post->slug = $this->generateEnglishSlug($title, $post->id);
        }
        $post->category_id = $categoryId;
        $post->excerpt = $validated['excerpt'] ?: Str::limit(strip_tags($validated['content']), 200);
        $post->content = $validated['content'];
        $post->owner_name = $user->name;
        $post->owner_phone = $user->phone ?: '';

        if ($isSubmit) {
            $post->status = 'pending';
            $post->mod_status = 'pending';
            $post->rejection_reason = null;
            $message = 'আপনার পোস্টটি সফলভাবে সাবমিট হয়েছে! অ্যাডমিন পর্যালোচনার জন্য জমা রাখা হয়েছে।';
        } else {
            $post->status = 'draft';
            $message = 'খসড়া লেখাটি সফলভাবে হালনাগাদ করা হয়েছে।';
        }

        try {
            $post->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AuthorBlogController update error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return back()->withInput()->with('error', 'পোস্টটি হালনাগাদের সময় ত্রুটি ঘটেছে: ' . $e->getMessage());
        }

        return redirect()->route('author.posts.index')->with('success', $message);
    }

    /**
     * Decode and save base64 image data to public storage.
     */
    private function saveBase64Image(string $base64Data, string $folder = 'blog'): ?string
    {
        if (empty($base64Data)) {
            return null;
        }

        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $type = 'jpg';
                }
            } else {
                $type = 'jpg';
            }

            $decoded = base64_decode($base64Data);
            if ($decoded === false) {
                return null;
            }

            $filename = $folder . '/photocard_' . time() . '_' . Str::random(8) . '.' . $type;
            Storage::disk('public')->put($filename, $decoded);

            return $filename;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Base64 image save error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a draft or rejected post by the author.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();
        $post = BlogPost::where('id', $id)
            ->where(function ($q) use ($user, $author) {
                $q->where('submitted_by', $user->id)
                  ->orWhere('author_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            })->firstOrFail();

        if ($post->status === 'published' || $post->mod_status === 'approved' || $post->status === 'pending') {
            return redirect()->route('author.posts.index')
                ->with('error', 'অনুমোদিত বা প্রকাশিত লেখা লেখক ডিলিট করতে পারবেন না। অনুমোদিত পোস্ট ডিলিট বা পরিবর্তন শুধুমাত্র অ্যাডমিন করতে পারবেন।');
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->forceDelete();

        return redirect()->route('author.posts.index')->with('success', 'ব্লগ পোস্টটি তালিকা থেকে মুছে ফেলা হয়েছে।');
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

    /**
     * Generate an aesthetic luxury photocard (.avif) when author doesn't attach an image.
     */
    protected function generateDefaultPhotocard(string $title, string $authorName): string
    {
        return \App\Services\ImageOptimizerService::generatePhotocardAndStore($title, $authorName, 'blog', 'public');
    }
}
