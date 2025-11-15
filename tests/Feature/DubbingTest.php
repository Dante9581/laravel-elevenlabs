<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Feature;

use DigitalCoreHub\LaravelElevenLabs\Data\DubbingResult;
use DigitalCoreHub\LaravelElevenLabs\Events\DubbingCompleted;
use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;
use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\DubbingEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Jobs\RunDubbingJob;
use DigitalCoreHub\LaravelElevenLabs\Tests\Fake\FakeDubbingProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class DubbingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Config::set('elevenlabs.api_key', 'test-api-key');
        Config::set('elevenlabs.base_url', 'https://api.elevenlabs.io/v1');
        Config::set('elevenlabs.default_voice', 'nova');
        Config::set('elevenlabs.default_format', 'mp3_44100_128');
        Config::set('elevenlabs.timeout', 30);
    }

    protected function getPackageProviders($app): array
    {
        return [
            \DigitalCoreHub\LaravelElevenLabs\ElevenLabsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'ElevenLabs' => \DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs::class,
        ];
    }

    /**
     * Replace DubbingEndpoint with FakeDubbingProvider for testing.
     */
    protected function useFakeProvider(): void
    {
        $this->app->singleton(DubbingEndpoint::class, function ($app) {
            $client = $app->make(ElevenLabsClient::class);

            return new FakeDubbingProvider($client);
        });
    }

    /**
     * Create a test video file.
     */
    protected function createTestVideoFile(string $path = 'test-video.mp4'): string
    {
        Storage::disk('local')->put($path, 'fake video content');

        return Storage::disk('local')->path($path);
    }

    /** @test */
    public function it_can_run_dubbing_with_fake_provider(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestVideoFile('input.mp4');

        $result = ElevenLabs::dubbing()
            ->source($filePath)
            ->target('tr')
            ->run();

        $this->assertInstanceOf(DubbingResult::class, $result);
        $this->assertNotEmpty($result->jobId);
        $this->assertEquals('processing', $result->status);
    }

    /** @test */
    public function it_can_use_storage_disk_for_source_file(): void
    {
        $this->useFakeProvider();

        Storage::disk('local')->put('videos/input.mp4', 'fake video content');

        $result = ElevenLabs::dubbing()
            ->source('videos/input.mp4', 'local')
            ->target('en')
            ->run();

        $this->assertInstanceOf(DubbingResult::class, $result);
        $this->assertNotEmpty($result->jobId);
    }

    /** @test */
    public function it_can_use_fluent_api_with_options(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestVideoFile('input.mp4');

        $result = ElevenLabs::dubbing()
            ->source($filePath)
            ->target('tr')
            ->options(['num_speakers' => 2, 'watermark' => false])
            ->run();

        $this->assertInstanceOf(DubbingResult::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_source_is_missing(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source file is required for dubbing.');

        ElevenLabs::dubbing()
            ->target('tr')
            ->run();
    }

    /** @test */
    public function it_throws_exception_when_target_is_missing(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestVideoFile('input.mp4');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Target language is required for dubbing.');

        ElevenLabs::dubbing()
            ->source($filePath)
            ->run();
    }

    /** @test */
    public function it_can_check_dubbing_status(): void
    {
        $this->useFakeProvider();

        $result = ElevenLabs::dubbing()->status('completed-job-id');

        $this->assertInstanceOf(DubbingResult::class, $result);
        $this->assertEquals('completed', $result->status);
        $this->assertNotEmpty($result->outputUrl);
        $this->assertNotNull($result->duration);
    }

    /** @test */
    public function dubbing_result_can_check_status(): void
    {
        $this->useFakeProvider();

        $result = ElevenLabs::dubbing()->status('completed-job-id');

        $this->assertTrue($result->isCompleted());
        $this->assertFalse($result->isInProgress());
        $this->assertFalse($result->isFailed());

        $processingResult = ElevenLabs::dubbing()->status('processing-job-id');
        $this->assertTrue($processingResult->isInProgress());
        $this->assertFalse($processingResult->isCompleted());
    }

    /** @test */
    public function it_can_dispatch_dubbing_job_to_queue(): void
    {
        $this->useFakeProvider();
        Queue::fake();

        $filePath = $this->createTestVideoFile('input.mp4');

        ElevenLabs::dubbing()
            ->source($filePath)
            ->target('tr')
            ->dispatch();

        Queue::assertPushed(RunDubbingJob::class);
    }

    /** @test */
    public function it_can_dispatch_dubbing_job_with_parameters(): void
    {
        $this->useFakeProvider();
        Queue::fake();

        ElevenLabs::dubbing()->dispatch('input.mp4', 'tr');

        Queue::assertPushed(RunDubbingJob::class, function ($job) {
            return $job->source === 'input.mp4' && $job->target === 'tr';
        });
    }

    /** @test */
    public function it_dispatches_event_when_dubbing_completes(): void
    {
        $this->useFakeProvider();
        Event::fake();

        $result = ElevenLabs::dubbing()->status('completed-job-id');

        Event::assertDispatched(DubbingCompleted::class, function ($event) use ($result) {
            return $event->result->jobId === $result->jobId;
        });
    }

    /** @test */
    public function it_can_reset_service_state(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestVideoFile('input.mp4');

        $service = ElevenLabs::dubbing()
            ->source($filePath)
            ->target('tr')
            ->options(['test' => 'value']);

        $service->reset();

        $newFilePath = $this->createTestVideoFile('new-input.mp4');

        $result = $service
            ->source($newFilePath)
            ->target('en')
            ->run();

        $this->assertInstanceOf(DubbingResult::class, $result);
    }

    /** @test */
    public function dubbing_result_can_be_converted_to_array(): void
    {
        $this->useFakeProvider();

        $result = ElevenLabs::dubbing()->status('completed-job-id');
        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('output_url', $array);
        $this->assertArrayHasKey('job_id', $array);
        $this->assertArrayHasKey('duration', $array);
    }

    /** @test */
    public function run_dubbing_job_executes_correctly(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestVideoFile('input.mp4');

        $job = new RunDubbingJob($filePath, null, 'tr', ['test' => 'option']);
        $job->handle();

        // If no exception is thrown, job executed successfully
        $this->assertTrue(true);
    }
}
