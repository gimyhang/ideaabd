<?php

declare(strict_types=1);

namespace Modules\Tag\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tag\Models\Tag;
use Modules\Tag\Services\TagService;

class TagController
{
    private TagService $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Display tags for frontend (tag cloud, popular tags, etc).
     *
     * @return View
     */
    public function index(): View
    {
        $popularTags = $this->tagService->getPopular(20);
        $allTags = $this->tagService->getCloud(50);

        return view('tag::frontend.index', compact('popularTags', 'allTags'));
    }

    /**
     * Get items by a specific tag.
     *
     * @param Request $request
     * @param Tag $tag
     * @return View
     */
    public function show(Request $request, Tag $tag): View
    {
        $sort = $request->query('sort', 'latest');
        $perPage = $request->query('per_page', 12);

        // Fetch items based on tag type
        $items = match ($tag->category) {
            'genre' => $tag->books()->paginate($perPage),
            'theme' => $tag->books()->paginate($perPage),
            'ebook' => $tag->ebooks()->paginate($perPage),
            default => $tag->taggables()->paginate($perPage),
        };

        return view('tag::frontend.show', compact('tag', 'items'));
    }

    /**
     * Search tags via API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->query('q', '');
        $limit = $request->query('limit', 10);

        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $tags = $this->tagService->search($search, $limit);

        return response()->json([
            'results' => $tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'category' => $tag->category,
                'count' => $tag->usage_count,
            ]),
        ]);
    }

    /**
     * Get popular tags.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $category = $request->query('category');

        $tags = $this->tagService->getPopular($limit, $category);

        return response()->json([
            'tags' => $tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'category' => $tag->category,
                'count' => $tag->usage_count,
                'color' => $tag->color,
            ]),
        ]);
    }

    /**
     * Get tag cloud.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cloud(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 50);

        $tags = $this->tagService->getCloud($limit);

        return response()->json([
            'tags' => $tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->usage_count,
                'color' => $tag->color,
                'size' => $this->calculateTagSize($tag->usage_count, $tags->max('usage_count')),
            ]),
        ]);
    }

    /**
     * Calculate tag size for cloud display.
     *
     * @param int $count
     * @param int $maxCount
     * @return string
     */
    private function calculateTagSize(int $count, int $maxCount): string
    {
        if ($maxCount === 0) return 'small';

        $percentage = ($count / $maxCount) * 100;

        if ($percentage >= 75) return 'huge';
        if ($percentage >= 50) return 'large';
        if ($percentage >= 25) return 'medium';
        return 'small';
    }
}
