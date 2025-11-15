<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Fake;

use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\DubbingEndpoint;

class FakeDubbingProvider extends DubbingEndpoint
{
    /**
     * Start fake dubbing job.
     */
    public function start(string $filePath, string $targetLanguage, ?array $options = null): array
    {
        return [
            'status' => 'processing',
            'job_id' => 'fake-job-'.time(),
            'output_url' => null,
            'duration' => null,
        ];
    }

    /**
     * Get fake dubbing status.
     */
    public function status(string $jobId): array
    {
        // Simulate completed job
        if (str_contains($jobId, 'completed')) {
            return [
                'status' => 'completed',
                'job_id' => $jobId,
                'output_url' => 'https://example.com/dubbed-output.mp4',
                'duration' => 120.5,
                'metadata' => [
                    'source_language' => 'en',
                    'target_language' => 'tr',
                ],
            ];
        }

        // Simulate in-progress job
        return [
            'status' => 'processing',
            'job_id' => $jobId,
            'output_url' => null,
            'duration' => null,
        ];
    }
}
