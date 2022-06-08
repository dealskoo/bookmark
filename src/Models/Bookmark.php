<?php

namespace Dealskoo\Bookmark\Models;

use Dealskoo\Bookmark\Events\Bookmarked;
use Dealskoo\Bookmark\Events\Unbookmarked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Bookmark extends Model
{

    protected $dispatchesEvents = [
        'created' => Bookmarked::class,
        'deleted' => Unbookmarked::class
    ];

    public function bookmarkable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function bookmarker()
    {
        return $this->user();
    }

    public function scopeWithType(Builder $builder, string $type)
    {
        return $builder->where('bookmarkable_type', app($type)->getMorphClass());
    }
}
