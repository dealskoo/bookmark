<?php

namespace Dealskoo\Bookmark\Events;

use Dealskoo\Bookmark\Models\Bookmark;

class Event
{
    public $bookmark;

    public function __construct(Bookmark $bookmark)
    {
        $this->bookmark = $bookmark;
    }
}
