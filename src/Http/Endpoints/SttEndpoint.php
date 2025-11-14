<?php

namespace DigitalCoreHub\LaravelElevenLabs\Http\Endpoints;

use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;

class SttEndpoint
{
    /**
     * Create a new SttEndpoint instance.
     */
    public function __construct(
        protected ElevenLabsClient $client
    ) {}

    /**
     * Transcribe audio file to text.
     */
    public function transcribe(
        string $filePath,
        ?string $modelId = null
    ): array {
        $endpoint = '/speech-to-text';

        $multipartData = [
            [
                'name' => 'audio',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        if ($modelId) {
            $multipartData[] = [
                'name' => 'model_id',
                'contents' => $modelId,
            ];
        }

        $response = $this->client->postMultipart($endpoint, $multipartData);

        // Close the file handle
        if (isset($multipartData[0]['contents']) && is_resource($multipartData[0]['contents'])) {
            fclose($multipartData[0]['contents']);
        }

        return $response;
    }
}
