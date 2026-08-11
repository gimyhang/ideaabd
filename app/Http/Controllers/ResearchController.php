<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Research\Models\ResearchPaper;
use Illuminate\Support\Facades\DB;

class ResearchController extends Controller
{
    public function index(Request $request)
    {
        $canUseResearch = false;

        try {
            $canUseResearch = DB::getSchemaBuilder()->hasTable('research_papers');
        } catch (\Throwable) {
            $canUseResearch = false;
        }

        $papers = collect();

        if ($canUseResearch) {
            $papers = ResearchPaper::query()
                ->with(['author'])
                ->where('is_published', true)
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->string('search')->trim()->value();
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('abstract', 'LIKE', "%{$search}%");
                })
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString();
        }

        return view('frontend.pages.research', compact('papers'));
    }

    public function show($slug)
    {
        $paper = ResearchPaper::query()
            ->with(['author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('frontend.pages.research-detail', compact('paper'));
    }
}
