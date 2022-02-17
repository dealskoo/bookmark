<?php

namespace Dealskoo\Bookmark\Tests;

use Dealskoo\Bookmark\Traits\Bookmarkable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Bookmarkable;

    protected $fillable = ['title'];
}
