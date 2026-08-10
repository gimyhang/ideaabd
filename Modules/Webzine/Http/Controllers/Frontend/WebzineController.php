<?php

namespace Modules\Webzine\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Webzine\Models\Webzine;

class WebzineController extends Controller
{
    public function index()
    {
        $webzines = Webzine::published()->latest('published_at')->paginate(12);
        return view('webzine::index', compact('webzines'));
    }

    public function show($slug)
    {
        $webzine = Webzine::where('slug', $slug)->published()->firstOrFail();
        $webzine->increment('view_count');
        
        $articles = $webzine->articles()->orderBy('order')->get();

        return view('webzine::show', compact('webzine', 'articles'));
    }

    public function read($slug)
    {
        $webzine = Webzine::where('slug', $slug)->published()->firstOrFail();
        $articles = $webzine->articles()->orderBy('order')->get();

        return view('webzine::read', compact('webzine', 'articles'));
    }
}
