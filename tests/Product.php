<?php

namespace Dealskoo\Bookmark\Tests;

use Dealskoo\Bookmark\Traits\Bookmarkable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Bookmarkable;

    protected $fillable = ['name'];
}
