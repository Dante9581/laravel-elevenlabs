<?php

namespace DigitalCoreHub\LaravelElevenLabs\Events;

use DigitalCoreHub\LaravelElevenLabs\Data\VoiceCollection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceSynced
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly VoiceCollection $voices
    ) {}
}
