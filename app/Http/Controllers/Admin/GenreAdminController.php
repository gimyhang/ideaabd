<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Book\Models\Book;
use Modules\Book\Models\Genre;

/**
 * Manage the genre list that the admin book form, the publisher portal, validation and filters all share.
 * A genre's slug is permanent (books store it); names, order and visibility can change freely.
 */
class GenreAdminController extends Controller
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    public function index(): View
    {
        $counts = Book::query()->whereNotNull('genre_category')->selectRaw('genre_category, COUNT(*) as c')->groupBy('genre_category')->pluck('c', 'genre_category');

        $genres = Genre::query()->orderBy('sort_order')->orderBy('id')->get()->map(function (Genre $g) use ($counts) {
            $g->books_count = (int) ($counts[$g->slug] ?? 0);

            return $g;
        });

        // slugs stored on books that have no genre row (typed by hand or left over): surfaced so nothing is invisible
        $orphans = collect($counts)->except($genres->pluck('slug')->all());

        return view('admin.genres.index', compact('genres', 'orphans'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug'    => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('genres', 'slug')],
            'name_en' => ['required', 'string', 'max:150'],
            'name_bn' => ['nullable', 'string', 'max:150'],
        ]);

        $genre = Genre::create($data + [
            'is_active'  => true,
            'sort_order' => ((int) Genre::max('sort_order')) + 10,
        ]);
        $this->accessService->log('genre_create', "Genre '{$genre->slug}' created");

        return response()->json(['success' => true, 'message' => 'Genre added.', 'genre' => $this->payload($genre)]);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $data = $request->validate([
            'name_en'   => ['sometimes', 'required', 'string', 'max:150'],
            'name_bn'   => ['sometimes', 'nullable', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $genre->update($data);
        $this->accessService->log('genre_update', "Genre '{$genre->slug}' updated");

        return response()->json(['success' => true, 'message' => 'Saved.', 'genre' => $this->payload($genre)]);
    }

    /** Set the order from the full list of ids as the page shows it. */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer', 'exists:genres,id']]);

        DB::transaction(function () use ($data) {
            foreach (array_values($data['ids']) as $i => $id) {
                Genre::whereKey($id)->update(['sort_order' => ($i + 1) * 10]);
            }
        });
        Genre::forgetCache();

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $used = $genre->booksCount();
        if ($used > 0) {
            return response()->json([
                'success' => false,
                'message' => "{$used} book(s) use this genre. Switch it off instead of deleting it.",
            ], 422);
        }

        $slug = $genre->slug;
        $genre->delete();
        $this->accessService->log('genre_delete', "Genre '{$slug}' deleted");

        return response()->json(['success' => true, 'message' => 'Genre deleted.']);
    }

    private function payload(Genre $g): array
    {
        return [
            'id' => $g->id, 'slug' => $g->slug, 'name_en' => $g->name_en, 'name_bn' => $g->name_bn,
            'is_active' => (bool) $g->is_active, 'sort_order' => $g->sort_order, 'books_count' => $g->booksCount(),
        ];
    }
}
