<?php

namespace DigitalCoreHub\LaravelElevenLabs\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ElevenLabsClient
{
    /**
     * Create a new ElevenLabsClient instance.
     */
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl,
        protected int $timeout = 30
    ) {}

    /**
     * Create a configured HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withHeaders([
                'xi-api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->baseUrl($this->baseUrl);
    }

    /**
     * Make a GET request.
     */
    public function get(string $endpoint, array $query = []): array
    {
        $response = $this->client()->get($endpoint, $query);

        if ($response->failed()) {
            throw \DigitalCoreHub\LaravelElevenLabs\Exceptions\ElevenLabsException::fromApiResponse(
                $response->json() ?? [],
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Make a POST request.
     */
    public function post(string $endpoint, array $data = []): array
    {
        $response = $this->client()->post($endpoint, $data);

        if ($response->failed()) {
            throw \DigitalCoreHub\LaravelElevenLabs\Exceptions\ElevenLabsException::fromApiResponse(
                $response->json() ?? [],
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Make a POST request and return binary content.
     */
    public function postBinary(string $endpoint, array $data = [], array $query = []): string
    {
        $request = $this->client();

        if (! empty($query)) {
            $request = $request->withQueryParameters($query);
        }

        $response = $request->post($endpoint, $data);

        if ($response->failed()) {
            throw \DigitalCoreHub\LaravelElevenLabs\Exceptions\ElevenLabsException::fromApiResponse(
                $response->json() ?? [],
                $response->status()
            );
        }

        return $response->body();
    }

    /**
     * Make a multipart POST request (for file uploads).
     */
    public function postMultipart(string $endpoint, array $data = [], array $query = []): array
    {
        $request = $this->client();

        if (! empty($query)) {
            $request = $request->withQueryParameters($query);
        }

        $response = $request->asMultipart()->post($endpoint, $data);

        if ($response->failed()) {
            throw \DigitalCoreHub\LaravelElevenLabs\Exceptions\ElevenLabsException::fromApiResponse(
                $response->json() ?? [],
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $endpoint): array
    {
        $response = $this->client()->delete($endpoint);

        if ($response->failed()) {
            throw \DigitalCoreHub\LaravelElevenLabs\Exceptions\ElevenLabsException::fromApiResponse(
                $response->json() ?? [],
                $response->status()
            );
        }

        return $response->json() ?? [];
    }
}
