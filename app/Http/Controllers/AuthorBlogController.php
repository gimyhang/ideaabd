<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Illuminate\Support\Facades\Storage;

class AuthorBlogController extends Controller
{
    /**
     * Store a new blog post created by the author.
     */
    public function store(Request $request)
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
            $message = 'আপনার ব্লগ পোস্টটি সফলভাবে জমা হয়েছে। অ্যাডমিন যাচাই করে তা প্রকাশ করবেন।';
        } else {
            $post->status = 'draft';
            $post->mod_status = 'pending';
            $message = 'ব্লগ পোস্টটি খসড়া (Draft) হিসেবে সংরক্ষিত হয়েছে।';
        }

        $post->save();

        return redirect()->route('my-account', ['tab' => 'author-blogs'])->with('success', $message);
    }

    /**
     * Update an existing draft or rejected blog post.
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        $post = BlogPost::where('id', $id)->where('author_id', $user->id)->firstOrFail();

        // Lock check: Once submitted or approved/published, author cannot edit!
        if ($post->status === 'pending' || $post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('my-account', ['tab' => 'author-blogs'])
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
            $message = 'পোস্টটি সফলভাবে পর্যালোচনার জন্য জমা দেওয়া হয়েছে।';
        } else {
            $post->status = 'draft';
            $message = 'খসড়া পোস্টটি হালনাগাদ করা হয়েছে।';
        }

        $post->save();

        return redirect()->route('my-account', ['tab' => 'author-blogs'])->with('success', $message);
    }

    /**
     * Delete a draft or rejected post by the author.
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        $post = BlogPost::where('id', $id)->where('author_id', $user->id)->firstOrFail();

        if ($post->status === 'published' || $post->mod_status === 'approved') {
            return redirect()->route('my-account', ['tab' => 'author-blogs'])
                ->with('error', 'প্রকাশিত লেখা সরাসরি মোছা সম্ভব নয়। অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->forceDelete();

        return redirect()->route('my-account', ['tab' => 'author-blogs'])->with('success', 'ব্লগ পোস্টটি মুছে ফেলা হয়েছে।');
    }

    private function bengaliToEnglish(string $text): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $text = str_replace($bengali, $english, $text);
        return Str::slug($text, '-', null);
    }
}
