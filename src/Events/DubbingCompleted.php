<?php

namespace DigitalCoreHub\LaravelElevenLabs\Events;

use DigitalCoreHub\LaravelElevenLabs\Data\DubbingResult;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DubbingCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly DubbingResult $result
    ) {}
}
