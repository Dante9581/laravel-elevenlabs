<?php

namespace DigitalCoreHub\LaravelElevenLabs\Services\Dubbing;

use DigitalCoreHub\LaravelElevenLabs\Data\DubbingResult;
use DigitalCoreHub\LaravelElevenLabs\Events\DubbingCompleted;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\DubbingEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Jobs\RunDubbingJob;
use Illuminate\Support\Facades\Storage;

class DubbingService
{
    protected ?string $source = null;

    protected ?string $disk = null;

    protected ?string $target = null;

    protected ?array $options = null;

    /**
     * Create a new DubbingService instance.
     */
    public function __construct(
        protected DubbingEndpoint $endpoint
    ) {}

    /**
     * Set the source file (video/audio).
     */
    public function source(string $filePath, ?string $disk = null): self
    {
        $this->source = $filePath;
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set the target language.
     */
    public function target(string $language): self
    {
        $this->target = $language;

        return $this;
    }

    /**
     * Set additional options.
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Run the dubbing job.
     */
    public function run(): DubbingResult
    {
        if (empty($this->source)) {
            throw new \InvalidArgumentException('Source file is required for dubbing.');
        }

        if (empty($this->target)) {
            throw new \InvalidArgumentException('Target language is required for dubbing.');
        }

        $filePath = $this->resolveFilePath($this->source, $this->disk);

        $response = $this->endpoint->start($filePath, $this->target, $this->options);

        $result = DubbingResult::fromArray($response);

        // Dispatch event if completed
        if ($result->isCompleted()) {
            event(new DubbingCompleted($result));
        }

        return $result;
    }

    /**
     * Dispatch dubbing job to queue.
     */
    public function dispatch(?string $source = null, ?string $target = null): void
    {
        if ($source) {
            $this->source($source);
        }

        if ($target) {
            $this->target($target);
        }

        if (empty($this->source) || empty($this->target)) {
            throw new \InvalidArgumentException('Source file and target language are required for dubbing.');
        }

        RunDubbingJob::dispatch($this->source, $this->disk, $this->target, $this->options);
    }

    /**
     * Get dubbing job status.
     */
    public function status(string $jobId): DubbingResult
    {
        $response = $this->endpoint->status($jobId);

        $result = DubbingResult::fromArray($response);

        // Dispatch event if completed
        if ($result->isCompleted()) {
            event(new DubbingCompleted($result));
        }

        return $result;
    }

    /**
     * Resolve file path from storage or absolute path.
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
        $this->source = null;
        $this->disk = null;
        $this->target = null;
        $this->options = null;

        return $this;
    }
}
