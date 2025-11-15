<?php

namespace DigitalCoreHub\LaravelElevenLabs\Facades;

use DigitalCoreHub\LaravelElevenLabs\Services\Dubbing\DubbingService;
use DigitalCoreHub\LaravelElevenLabs\Services\STT\SttService;
use DigitalCoreHub\LaravelElevenLabs\Services\TTS\TtsService;
use DigitalCoreHub\LaravelElevenLabs\Services\Voices\VoiceService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static TtsService tts()
 * @method static SttService stt()
 * @method static VoiceService voices()
 * @method static DubbingService dubbing()
 *
 * @see \DigitalCoreHub\LaravelElevenLabs\Services\TTS\TtsService
 * @see \DigitalCoreHub\LaravelElevenLabs\Services\STT\SttService
 * @see \DigitalCoreHub\LaravelElevenLabs\Services\Voices\VoiceService
 * @see \DigitalCoreHub\LaravelElevenLabs\Services\Dubbing\DubbingService
 */
class ElevenLabs extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'elevenlabs';
    }

    /**
     * Get the TTS service instance.
     */
    public static function tts(): TtsService
    {
        return app(TtsService::class);
    }

    /**
     * Get the STT service instance.
     */
    public static function stt(): SttService
    {
        return app(SttService::class);
    }

    /**
     * Get the Voices service instance.
     */
    public static function voices(): VoiceService
    {
        return app(VoiceService::class);
    }

    /**
     * Get the Dubbing service instance.
     */
    public static function dubbing(): DubbingService
    {
        return app(DubbingService::class);
    }
}
