<?php

namespace DigitalCoreHub\LaravelElevenLabs\Http\Endpoints;

use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;

class VoicesEndpoint
{
    /**
     * Create a new VoicesEndpoint instance.
     */
    public function __construct(
        protected ElevenLabsClient $client
    ) {}

    /**
     * Get all voices.
     */
    public function list(): array
    {
        return $this->client->get('/voices');
    }

    /**
     * Get a specific voice by ID.
     */
    public function get(string $voiceId): array
    {
        return $this->client->get("/voices/{$voiceId}");
    }

    /**
     * Create a custom voice.
     */
    public function create(string $name, array $files, ?string $description = null, ?array $labels = null): array
    {
        $multipartData = [
            [
                'name' => 'name',
                'contents' => $name,
            ],
        ];

        foreach ($files as $index => $file) {
            $filePath = is_string($file) ? $file : $file['path'];
            $fileName = is_string($file) ? basename($file) : ($file['name'] ?? basename($filePath));

            if (! file_exists($filePath)) {
                throw new \InvalidArgumentException("File not found: {$filePath}");
            }

            $multipartData[] = [
                'name' => 'files',
                'contents' => fopen($filePath, 'r'),
                'filename' => $fileName,
            ];
        }

        if ($description) {
            $multipartData[] = [
                'name' => 'description',
                'contents' => $description,
            ];
        }

        if ($labels) {
            $multipartData[] = [
                'name' => 'labels',
                'contents' => json_encode($labels),
            ];
        }

        $response = $this->client->postMultipart('/voices/add', $multipartData);

        // Close file handles
        foreach ($multipartData as $item) {
            if (isset($item['contents']) && is_resource($item['contents'])) {
                fclose($item['contents']);
            }
        }

        return $response;
    }

    /**
     * Delete a voice.
     */
    public function delete(string $voiceId): bool
    {
        $this->client->delete("/voices/{$voiceId}");

        return true;
    }
}

