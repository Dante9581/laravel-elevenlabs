<?php

namespace DigitalCoreHub\LaravelElevenLabs\Services\STT;

use DigitalCoreHub\LaravelElevenLabs\Data\TranscriptionResult;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\SttEndpoint;
use Illuminate\Support\Facades\Storage;

class SttService
{
    protected ?string $filePath = null;

    protected ?string $disk = null;

    protected ?string $modelId = null;

    /**
     * Create a new SttService instance.
     */
    public function __construct(
        protected SttEndpoint $endpoint
    ) {}

    /**
     * Set the audio file to transcribe.
     */
    public function file(string $filePath, ?string $disk = null): self
    {
        $this->filePath = $filePath;
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set the model ID to use for transcription.
     */
    public function model(string $modelId): self
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Transcribe the audio file and return a TranscriptionResult.
     */
    public function transcribe(): TranscriptionResult
    {
        if (empty($this->filePath)) {
            throw new \InvalidArgumentException('File path is required for STT transcription.');
        }

        // Get the full file path
        $fullPath = $this->getFullFilePath();

        if (! file_exists($fullPath)) {
            throw new \InvalidArgumentException("File not found: {$fullPath}");
        }

        // Call the endpoint
        $response = $this->endpoint->transcribe($fullPath, $this->modelId);

        // Extract data from response
        $text = $response['text'] ?? '';
        $words = $response['words'] ?? null;
        $confidence = $response['confidence'] ?? null;

        return new TranscriptionResult(
            text: $text,
            words: $words,
            confidence: $confidence
        );
    }

    /**
     * Get the full file path (from storage or absolute path).
     */
    protected function getFullFilePath(): string
    {
        // If disk is specified, get from storage
        if ($this->disk !== null) {
            $storage = Storage::disk($this->disk);

            if (! $storage->exists($this->filePath)) {
                throw new \InvalidArgumentException("File not found in storage: {$this->filePath}");
            }

            return $storage->path($this->filePath);
        }

        // Check if it's an absolute path
        if (str_starts_with($this->filePath, '/')) {
            return $this->filePath;
        }

        // Assume it's a relative path from storage/app
        $storage = Storage::disk('local');

        if (! $storage->exists($this->filePath)) {
            throw new \InvalidArgumentException("File not found: {$this->filePath}");
        }

        return $storage->path($this->filePath);
    }

    /**
     * Reset the service state.
     */
    public function reset(): self
    {
        $this->filePath = null;
        $this->disk = null;
        $this->modelId = null;

        return $this;
    }
}
