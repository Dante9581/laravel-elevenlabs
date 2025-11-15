<?php

namespace DigitalCoreHub\LaravelElevenLabs\Data;

class DubbingResult
{
    /**
     * Create a new DubbingResult instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $outputUrl = null,
        public readonly ?string $jobId = null,
        public readonly ?float $duration = null,
        public readonly ?array $metadata = null
    ) {}

    /**
     * Create a DubbingResult instance from API response.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 'pending',
            outputUrl: $data['output_url'] ?? $data['outputUrl'] ?? null,
            jobId: $data['job_id'] ?? $data['jobId'] ?? null,
            duration: $data['duration'] ?? null,
            metadata: $data['metadata'] ?? null
        );
    }

    /**
     * Check if dubbing is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->status === 'success';
    }

    /**
     * Check if dubbing is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'processing' || $this->status === 'in_progress';
    }

    /**
     * Check if dubbing failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'error';
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'output_url' => $this->outputUrl,
            'job_id' => $this->jobId,
            'duration' => $this->duration,
            'metadata' => $this->metadata,
        ];
    }
}
