<?php

namespace Dealskoo\Bookmark\Tests;

use Dealskoo\Bookmark\Traits\Bookmarker;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Bookmarker;

    protected $fillable = ['name'];
}
