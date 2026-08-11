<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Author\Models\Author;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    public function index()
    {
        $canUsePublishers = false;

        try {
            $canUsePublishers = DB::getSchemaBuilder()->hasTable('publishers');
        } catch (\Throwable) {
            $canUsePublishers = false;
        }

        $publishers = collect();

        if ($canUsePublishers) {
            $publishers = Author::query()
                ->where('is_publisher', true)
                ->where('is_active', true)
                ->paginate(12);
        }

        return view('frontend.pages.publishers', compact('publishers'));
    }

    public function show($slug)
    {
        $publisher = Author::query()
            ->where('slug', $slug)
            ->where('is_publisher', true)
            ->where('is_active', true)
            ->firstOrFail();

        $books = $publisher->books()->paginate(12);

        return view('frontend.pages.publisher-detail', compact('publisher', 'books'));
    }
}
