<?php

namespace Modules\Webzine\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Webzine\Models\Webzine;

class WebzineController extends Controller
{
    public function index(Request $request)
    {
        $query = Webzine::query()->where('is_published', true);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('issue_number', 'LIKE', "%{$search}%");
            });
        }

        $allWebzines = $query->latest('publication_date')->latest('id')->get();

        // Group webzines by their magazine category / magazine name
        $magazineCategories = $allWebzines->groupBy(function ($item) {
            return $item->category ?: 'আইডিয়া সাহিত্য সাময়িকী';
        });

        $webzines = $allWebzines;

        return view('webzine::index', compact('webzines', 'magazineCategories'));
    }

    public function show($slug)
    {
        $webzine = Webzine::where('slug', $slug)->where('is_published', true)->first();

        if (!$webzine && is_numeric($slug)) {
            $webzine = Webzine::where('id', $slug)->where('is_published', true)->first();
        }

        if (!$webzine) {
            $webzine = Webzine::where('is_published', true)
                ->where(function($q) use ($slug) {
                    $q->where('title', 'like', "%{$slug}%")
                      ->orWhere('slug', 'like', "%{$slug}%");
                })->first();
        }

        if (!$webzine) {
            abort(404, 'অনুরোধকৃত সাহিত্য সাময়িকীটি পাওয়া যায়নি।');
        }

        try {
            $webzine->increment('view_count');
        } catch (\Throwable) {}

        $articles = $webzine->articles()->orderBy('order')->get();

        $relatedIssues = Webzine::where('category', $webzine->category)
            ->where('id', '!=', $webzine->id)
            ->where('is_published', true)
            ->take(4)
            ->get();

        $epubUrl = $webzine->epub_url;
        $fileUrl = $epubUrl;
        $readerType = $epubUrl ? 'epub' : ($articles->count() ? 'articles' : (!empty($webzine->description) ? 'description' : 'preview'));

        return view('webzine::show', compact('webzine', 'articles', 'relatedIssues', 'epubUrl', 'fileUrl', 'readerType'));
    }

    public function read($slug)
    {
        $webzine = Webzine::where('slug', $slug)->where('is_published', true)->first();

        if (!$webzine && is_numeric($slug)) {
            $webzine = Webzine::where('id', $slug)->where('is_published', true)->first();
        }

        if (!$webzine) {
            $webzine = Webzine::where('is_published', true)
                ->where(function($q) use ($slug) {
                    $q->where('title', 'like', "%{$slug}%")
                      ->orWhere('slug', 'like', "%{$slug}%");
                })->first();
        }

        if (!$webzine) {
            abort(404, 'অনুরোধকৃত সাহিত্য সাময়িকীটি পাওয়া যায়নি।');
        }

        $articles = $webzine->articles()->orderBy('order')->get();

        $epubUrl = $webzine->epub_url;
        $fileUrl = $epubUrl;
        $readerType = $epubUrl ? 'epub' : ($articles->count() ? 'articles' : (!empty($webzine->description) ? 'description' : 'preview'));

        return view('webzine::read', compact('webzine', 'articles', 'epubUrl', 'fileUrl', 'readerType'));
    }
}
