<?php

declare(strict_types=1);

namespace Modules\Tag\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Tag\Models\Tag;

class TagService
{
    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        return Tag::create($data);
    }

    /**
     * Update a tag.
     *
     * @param Tag $tag
     * @param array $data
     * @return Tag
     */
    public function update(Tag $tag, array $data): Tag
    {
        if (isset($data['name']) && $data['name'] !== $tag->name) {
            $data['slug'] = Str::slug($data['name']);
        }
        $tag->update($data);
        return $tag;
    }

    /**
     * Attach tags to a model.
     *
     * @param Model $model
     * @param array $tagIds
     * @return void
     */
    public function attachTags(Model $model, array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            if (!$model->tags()->where('tag_id', $tagId)->exists()) {
                $model->tags()->attach($tagId);
                Tag::find($tagId)->incrementUsageCount();
            }
        }
    }

    /**
     * Detach tags from a model.
     *
     * @param Model $model
     * @param array $tagIds
     * @return void
     */
    public function detachTags(Model $model, array $tagIds = []): void
    {
        $tags = $tagIds ? $model->tags()->whereIn('tag_id', $tagIds)->get() : $model->tags;
        foreach ($tags as $tag) {
            $tag->decrementUsageCount();
        }
        $model->tags()->detach($tagIds);
    }

    /**
     * Sync tags for a model.
     *
     * @param Model $model
     * @param array $tagIds
     * @return void
     */
    public function syncTags(Model $model, array $tagIds): void
    {
        $currentTags = $model->tags()->pluck('id')->toArray();
        $tagsToRemove = array_diff($currentTags, $tagIds);
        $tagsToAdd = array_diff($tagIds, $currentTags);

        if (!empty($tagsToRemove)) {
            $this->detachTags($model, $tagsToRemove);
        }

        if (!empty($tagsToAdd)) {
            $this->attachTags($model, $tagsToAdd);
        }
    }

    /**
     * Get related items by tags.
     *
     * @param Model $model
     * @param int $limit
     * @return mixed
     */
    public function getRelatedByTags(Model $model, int $limit = 10)
    {
        $tagIds = $model->tags()->pluck('id')->toArray();

        return $model::whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })
            ->where('id', '!=', $model->id)
            ->take($limit)
            ->get();
    }

    /**
     * Get popular tags.
     *
     * @param int $limit
     * @param string|null $category
     * @return mixed
     */
    public function getPopular(int $limit = 10, ?string $category = null)
    {
        $query = Tag::active()->orderBy('usage_count', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        return $query->take($limit)->get();
    }

    /**
     * Search tags by name.
     *
     * @param string $search
     * @param int $limit
     * @return mixed
     */
    public function search(string $search, int $limit = 10)
    {
        return Tag::active()
            ->where('name', 'like', "%{$search}%")
            ->orderBy('usage_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get tag cloud.
     *
     * @param int $limit
     * @return mixed
     */
    public function getCloud(int $limit = 50)
    {
        return Tag::active()
            ->orderBy('usage_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     * @return bool|null
     */
    public function delete(Tag $tag): ?bool
    {
        return $tag->delete();
    }
}
