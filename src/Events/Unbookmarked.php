<?php

namespace Dealskoo\Bookmark\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Unbookmarked extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
