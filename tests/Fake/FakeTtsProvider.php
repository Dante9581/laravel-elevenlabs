<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Fake;

use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\TtsEndpoint;

class FakeTtsProvider extends TtsEndpoint
{
    /**
     * Generate fake text-to-speech audio.
     */
    public function generate(
        string $text,
        string $voiceId,
        string $format = 'mp3_44100_128',
        ?array $modelSettings = null
    ): string {
        // Return fake audio content (simulated binary data)
        return base64_encode("fake_audio_content_for_{$text}_with_voice_{$voiceId}");
    }
}
