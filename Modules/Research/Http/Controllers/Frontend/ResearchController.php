<?php

namespace Modules\Research\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Research\Models\ResearchPaper;

class ResearchController extends Controller
{
    public function index()
    {
        $papers = ResearchPaper::published()->latest('published_at')->paginate(12);
        return view('research::index', compact('papers'));
    }

    public function show($slug)
    {
        $paper = ResearchPaper::where('slug', $slug)->published()->firstOrFail();
        $paper->increment('view_count');
        
        $related = ResearchPaper::published()
            ->where('category', $paper->category)
            ->where('id', '!=', $paper->id)
            ->limit(4)
            ->get();

        return view('research::show', compact('paper', 'related'));
    }

    public function download($slug)
    {
        $paper = ResearchPaper::where('slug', $slug)->published()->firstOrFail();
        $paper->increment('download_count');

        if ($paper->pdf_file_path && file_exists(storage_path('app/' . $paper->pdf_file_path))) {
            return response()->download(storage_path('app/' . $paper->pdf_file_path));
        }

        return back()->with('error', 'ফাইল পাওয়া যায়নি।');
    }
}
