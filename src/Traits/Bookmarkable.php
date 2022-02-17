<?php

namespace Dealskoo\Bookmark\Traits;

use Dealskoo\Bookmark\Models\Bookmark;
use Illuminate\Database\Eloquent\Model;

trait Bookmarkable
{
    public function isBookmarkedBy(Model $user)
    {
        if (is_a($user, config('auth.providers.users.model'))) {
            if ($this->relationLoaded('bookmarkers')) {
                return $this->bookmarkers->contains($user);
            }
            return $this->bookmarkers()->where('user_id', $user->getKey())->exists();
        }
        return false;
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function bookmarkers()
    {
        return $this->belongsToMany(config('auth.providers.users.model'), 'bookmarks', 'bookmarkable_id', 'user_id')->where('bookmarkable_type', $this->getMorphClass());
    }
}
