<?php

namespace DigitalCoreHub\LaravelElevenLabs\Services\Voices;

use DigitalCoreHub\LaravelElevenLabs\Data\Voice;
use DigitalCoreHub\LaravelElevenLabs\Data\VoiceCollection;
use DigitalCoreHub\LaravelElevenLabs\Events\VoiceCreated;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\VoicesEndpoint;
use Illuminate\Support\Facades\Storage;

class VoiceService
{
    protected ?string $name = null;
    protected array $files = [];
    protected ?string $description = null;
    protected ?array $labels = null;

    /**
     * Create a new VoiceService instance.
     */
    public function __construct(
        protected VoicesEndpoint $endpoint
    ) {}

    /**
     * Set the voice name.
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the voice files.
     */
    public function files(array $files): self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Set the voice description.
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the voice labels.
     */
    public function labels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Get all voices.
     */
    public function list(): VoiceCollection
    {
        $response = $this->endpoint->list();

        return VoiceCollection::fromArray($response);
    }

    /**
     * Get a specific voice by ID.
     */
    public function get(string $voiceId): Voice
    {
        $response = $this->endpoint->get($voiceId);

        return Voice::fromArray($response);
    }

    /**
     * Create a custom voice.
     */
    public function create(): Voice
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Voice name is required.');
        }

        if (empty($this->files)) {
            throw new \InvalidArgumentException('At least one file is required to create a voice.');
        }

        // Resolve file paths (support storage disks and absolute paths)
        $resolvedFiles = $this->resolveFiles($this->files);

        $response = $this->endpoint->create(
            name: $this->name,
            files: $resolvedFiles,
            description: $this->description,
            labels: $this->labels
        );

        $voice = Voice::fromArray($response);

        // Dispatch event
        event(new VoiceCreated($voice));

        return $voice;
    }

    /**
     * Delete a voice.
     */
    public function delete(string $voiceId): bool
    {
        return $this->endpoint->delete($voiceId);
    }

    /**
     * Sync voices from API (for queue usage).
     */
    public function sync(): VoiceCollection
    {
        $voices = $this->list();

        // Dispatch event for each voice (or batch)
        event(new \DigitalCoreHub\LaravelElevenLabs\Events\VoiceSynced($voices));

        return $voices;
    }

    /**
     * Resolve file paths from various sources.
     */
    protected function resolveFiles(array $files): array
    {
        $resolved = [];

        foreach ($files as $file) {
            if (is_string($file)) {
                // Simple string path
                $resolved[] = $this->resolveFilePath($file);
            } elseif (is_array($file)) {
                // Array with path and optional disk
                $path = $file['path'] ?? $file[0] ?? null;
                $disk = $file['disk'] ?? $file[1] ?? null;

                if (! $path) {
                    throw new \InvalidArgumentException('File path is required in file array.');
                }

                $resolved[] = $this->resolveFilePath($path, $disk);
            } else {
                throw new \InvalidArgumentException('Invalid file format. Expected string or array.');
            }
        }

        return $resolved;
    }

    /**
     * Resolve a single file path.
     */
    protected function resolveFilePath(string $path, ?string $disk = null): string
    {
        // If it's an absolute path, return as is
        if (str_starts_with($path, '/')) {
            if (! file_exists($path)) {
                throw new \InvalidArgumentException("File not found: {$path}");
            }

            return $path;
        }

        // If disk is specified, get from storage
        if ($disk !== null) {
            $storage = Storage::disk($disk);

            if (! $storage->exists($path)) {
                throw new \InvalidArgumentException("File not found in storage: {$path} (disk: {$disk})");
            }

            return $storage->path($path);
        }

        // Default to local storage
        $storage = Storage::disk('local');

        if (! $storage->exists($path)) {
            throw new \InvalidArgumentException("File not found: {$path}");
        }

        return $storage->path($path);
    }

    /**
     * Reset the service state.
     */
    public function reset(): self
    {
        $this->name = null;
        $this->files = [];
        $this->description = null;
        $this->labels = null;

        return $this;
    }
}

