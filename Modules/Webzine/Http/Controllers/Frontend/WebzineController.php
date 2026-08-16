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
        $webzine = Webzine::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $webzine->increment('view_count');

        $articles = $webzine->articles()->orderBy('order')->get();

        $relatedIssues = Webzine::where('category', $webzine->category)
            ->where('id', '!=', $webzine->id)
            ->where('is_published', true)
            ->take(4)
            ->get();

        return view('webzine::show', compact('webzine', 'articles', 'relatedIssues'));
    }

    public function read($slug)
    {
        $webzine = Webzine::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $articles = $webzine->articles()->orderBy('order')->get();

        return view('webzine::read', compact('webzine', 'articles'));
    }
}
