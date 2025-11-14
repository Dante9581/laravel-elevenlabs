<?php

namespace DigitalCoreHub\LaravelElevenLabs\Http\Endpoints;

use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;

class TtsEndpoint
{
    /**
     * Create a new TtsEndpoint instance.
     */
    public function __construct(
        protected ElevenLabsClient $client
    ) {}

    /**
     * Generate text-to-speech audio.
     */
    public function generate(
        string $text,
        string $voiceId,
        string $format = 'mp3_44100_128',
        ?array $modelSettings = null
    ): string {
        $data = [
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => $modelSettings ?? [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ];

        $endpoint = "/text-to-speech/{$voiceId}";

        $query = [];
        if ($format) {
            $query['output_format'] = $format;
        }

        return $this->client->postBinary($endpoint, $data, $query);
    }
}
