<?php

namespace Modules\Publisher\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Publisher\Models\Publisher;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::where('is_active', true)->where('is_verified', true)->paginate(12);
        return view('publisher::index', compact('publishers'));
    }

    public function show($slug)
    {
        $publisher = Publisher::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $books = $publisher->books()->where('is_active', true)->paginate(12);
        return view('publisher::show', compact('publisher', 'books'));
    }
}
