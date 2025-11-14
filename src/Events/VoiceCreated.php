<?php

namespace DigitalCoreHub\LaravelElevenLabs\Events;

use DigitalCoreHub\LaravelElevenLabs\Data\Voice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Voice $voice
    ) {}
}

