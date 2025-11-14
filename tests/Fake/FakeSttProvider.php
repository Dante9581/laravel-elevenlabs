<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Fake;

use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\SttEndpoint;

class FakeSttProvider extends SttEndpoint
{
    /**
     * Generate fake transcription result.
     */
    public function transcribe(
        string $filePath,
        ?string $modelId = null
    ): array {
        $filename = basename($filePath);

        return [
            'text' => "This is a fake transcription of {$filename}",
            'words' => [
                [
                    'word' => 'This',
                    'start' => 0.0,
                    'end' => 0.5,
                ],
                [
                    'word' => 'is',
                    'start' => 0.5,
                    'end' => 0.8,
                ],
                [
                    'word' => 'a',
                    'start' => 0.8,
                    'end' => 1.0,
                ],
                [
                    'word' => 'fake',
                    'start' => 1.0,
                    'end' => 1.5,
                ],
                [
                    'word' => 'transcription',
                    'start' => 1.5,
                    'end' => 2.5,
                ],
            ],
            'confidence' => 0.95,
        ];
    }
}
