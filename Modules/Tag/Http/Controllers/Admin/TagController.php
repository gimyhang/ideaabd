<?php

declare(strict_types=1);

namespace Modules\Tag\Http\Controllers\Admin;

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
     * Display a listing of tags.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Tag::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $tags = $query->paginate(15);

        return view('tag::admin.index', compact('tags'));
    }

    /**
     * Show the form for creating a new tag.
     *
     * @return View
     */
    public function create(): View
    {
        return view('tag::admin.create');
    }

    /**
     * Store a newly created tag in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'color' => 'nullable|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        try {
            $tag = $this->tagService->create($validated);
            return response()->json(['message' => 'Tag created successfully', 'tag' => $tag], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified tag.
     *
     * @param Tag $tag
     * @return View
     */
    public function show(Tag $tag): View
    {
        return view('tag::admin.show', compact('tag'));
    }

    /**
     * Show the form for editing the specified tag.
     *
     * @param Tag $tag
     * @return View
     */
    public function edit(Tag $tag): View
    {
        return view('tag::admin.edit', compact('tag'));
    }

    /**
     * Update the specified tag in storage.
     *
     * @param Request $request
     * @param Tag $tag
     * @return JsonResponse
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags,name,' . $tag->id . '|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'color' => 'nullable|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        try {
            $updatedTag = $this->tagService->update($tag, $validated);
            return response()->json(['message' => 'Tag updated successfully', 'tag' => $updatedTag]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified tag.
     *
     * @param Tag $tag
     * @return JsonResponse
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            $this->tagService->delete($tag);
            return response()->json(['message' => 'Tag deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
