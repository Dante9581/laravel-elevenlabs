<?php

namespace DigitalCoreHub\LaravelElevenLabs\Http\Endpoints;

use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;

class DubbingEndpoint
{
    /**
     * Create a new DubbingEndpoint instance.
     */
    public function __construct(
        protected ElevenLabsClient $client
    ) {}

    /**
     * Start a dubbing job.
     */
    public function start(string $filePath, string $targetLanguage, ?array $options = null): array
    {
        $multipartData = [
            [
                'name' => 'target_lang',
                'contents' => $targetLanguage,
            ],
        ];

        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $multipartData[] = [
            'name' => 'file',
            'contents' => fopen($filePath, 'r'),
            'filename' => basename($filePath),
        ];

        if ($options) {
            foreach ($options as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $multipartData[] = [
                    'name' => $key,
                    'contents' => (string) $value,
                ];
            }
        }

        $response = $this->client->postMultipart('/dubbing', $multipartData);

        // Close file handle
        foreach ($multipartData as $item) {
            if (isset($item['contents']) && is_resource($item['contents'])) {
                fclose($item['contents']);
            }
        }

        return $response;
    }

    /**
     * Get dubbing job status.
     */
    public function status(string $jobId): array
    {
        return $this->client->get("/dubbing/{$jobId}");
    }
}
