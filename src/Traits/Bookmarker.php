<?php

namespace Dealskoo\Bookmark\Traits;

use Dealskoo\Bookmark\Models\Bookmark;
use Illuminate\Database\Eloquent\Model;

trait Bookmarker
{
    public function bookmark(Model $model)
    {
        $attributes = [
            'bookmarkable_type' => $model->getMorphClass(),
            'bookmarkable_id' => $model->getKey(),
            'user_id' => $this->getKey(),
        ];
        return Bookmark::query()->where($attributes)->firstOr(function () use ($attributes) {
            return Bookmark::unguarded(function () use ($attributes) {
                if ($this->relationLoaded('bookmarks')) {
                    $this->unsetRelation('bookmarks');
                }
                return Bookmark::query()->create($attributes);
            });
        });
    }

    public function unbookmark(Model $model)
    {
        $bookmark = Bookmark::query()
            ->where('bookmarkable_id', $model->getKey())
            ->where('bookmarkable_type', $model->getMorphClass())
            ->where('user_id', $this->getKey())
            ->first();
        if ($bookmark) {
            if ($this->relationLoaded('bookmarks')) {
                $this->unsetRelation('bookmarks');
            }
            return $bookmark->delete();
        }
        return true;
    }

    public function toggleBookmark(Model $model)
    {
        return $this->hasBookmarked($model) ? $this->unbookmark($model) : $this->bookmark($model);
    }

    public function hasBookmarked(Model $model)
    {
        $bookmarks = $this->relationLoaded('bookmarks') ? $this->bookmarks : $this->bookmarks();
        return $bookmarks->where('bookmarkable_id', $model->getKey())->where('bookmarkable_type', $model->getMorphClass())->count() > 0;
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'user_id', $this->getKeyName());
    }

    public function getBookmarkedItems(string $model)
    {
        return app($model)->whereHas('bookmarks', function ($q) {
            return $q->where('user_id', $this->getKey());
        });
    }
}
