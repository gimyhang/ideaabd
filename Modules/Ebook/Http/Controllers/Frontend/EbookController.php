<?php

namespace Modules\Ebook\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ebook\Models\Ebook;
use Illuminate\Support\Facades\DB;

class EbookController extends Controller
{
    public function index(Request $request)
    {
        $canUseEbooks = false;

        try {
            $canUseEbooks = DB::getSchemaBuilder()->hasTable('ebooks');
        } catch (\Throwable) {
            $canUseEbooks = false;
        }

        $ebooks = collect();

        if ($canUseEbooks) {
            $ebooks = Ebook::query()
                ->with(['author', 'publisher'])
                ->where('is_active', true)
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->string('search')->trim()->value();
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->when($request->filled('sort'), function ($q) use ($request) {
                    match ($request->string('sort')->value()) {
                        'price_low'   => $q->orderBy('price', 'asc'),
                        'price_high'  => $q->orderBy('price', 'desc'),
                        'newest'      => $q->latest('published_at'),
                        default       => $q->latest(),
                    };
                }, fn ($q) => $q->latest())
                ->paginate(12)
                ->withQueryString();
        }

        return view('ebook::frontend.index', compact('ebooks'));
    }

    public function show(string $slug)
    {
        $ebook = Ebook::query()
            ->with(['author', 'publisher'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedEbooks = Ebook::query()
            ->where('id', '!=', $ebook->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('ebook::frontend.show', compact('ebook', 'relatedEbooks'));
    }

    public function read(string $slug)
    {
        $ebook = Ebook::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (!$ebook->epub_file_path) {
            abort(404, 'ই-বুক ফাইল পাওয়া যায়নি');
        }

        return view('ebook::frontend.read', compact('ebook'));
    }
}
