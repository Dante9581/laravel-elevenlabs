<?php

namespace DigitalCoreHub\LaravelElevenLabs\Jobs;

use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDubbingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $source,
        public ?string $disk = null,
        public string $target = 'en',
        public ?array $options = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ElevenLabs::dubbing()
            ->source($this->source, $this->disk)
            ->target($this->target)
            ->options($this->options ?? [])
            ->run();
    }
}
