<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Feature;

use DigitalCoreHub\LaravelElevenLabs\Data\TranscriptionResult;
use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;
use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\SttEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Tests\Fake\FakeSttProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class SttTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up fake storage
        Storage::fake('local');

        // Configure the package
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
     * Replace SttEndpoint with FakeSttProvider for testing.
     */
    protected function useFakeProvider(): void
    {
        $this->app->singleton(SttEndpoint::class, function ($app) {
            $client = $app->make(ElevenLabsClient::class);
            return new FakeSttProvider($client);
        });
    }

    /**
     * Create a test audio file.
     */
    protected function createTestAudioFile(string $path = 'test-audio.wav'): string
    {
        Storage::disk('local')->put($path, 'fake audio content');
        return Storage::disk('local')->path($path);
    }

    /** @test */
    public function it_can_transcribe_audio_file_with_fake_provider(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('audio.wav');

        $result = ElevenLabs::stt()
            ->file($filePath)
            ->transcribe();

        $this->assertInstanceOf(TranscriptionResult::class, $result);
        $this->assertNotEmpty($result->getText());
        $this->assertStringContainsString('fake transcription', $result->getText());
    }

    /** @test */
    public function it_can_use_storage_disk_for_file(): void
    {
        $this->useFakeProvider();

        Storage::disk('local')->put('audio/test.wav', 'fake audio content');

        $result = ElevenLabs::stt()
            ->file('audio/test.wav', 'local')
            ->transcribe();

        $this->assertInstanceOf(TranscriptionResult::class, $result);
        $this->assertNotEmpty($result->getText());
    }

    /** @test */
    public function it_returns_transcription_result_with_all_fields(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('audio.wav');

        $result = ElevenLabs::stt()
            ->file($filePath)
            ->transcribe();

        $this->assertInstanceOf(TranscriptionResult::class, $result);
        $this->assertNotEmpty($result->getText());
        $this->assertIsArray($result->getWords());
        $this->assertIsFloat($result->getConfidence());
        $this->assertEquals(0.95, $result->getConfidence());
    }

    /** @test */
    public function it_can_use_model_parameter(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('audio.wav');

        $result = ElevenLabs::stt()
            ->file($filePath)
            ->model('eleven_multilingual_v2')
            ->transcribe();

        $this->assertInstanceOf(TranscriptionResult::class, $result);
        $this->assertNotEmpty($result->getText());
    }

    /** @test */
    public function it_throws_exception_when_file_is_missing(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');

        ElevenLabs::stt()
            ->file('non-existent-file.wav')
            ->transcribe();
    }

    /** @test */
    public function it_throws_exception_when_file_path_is_not_provided(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File path is required for STT transcription.');

        ElevenLabs::stt()
            ->transcribe();
    }

    /** @test */
    public function it_can_reset_service_state(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('audio.wav');

        $service = ElevenLabs::stt()
            ->file($filePath)
            ->model('test-model');

        $service->reset();

        // After reset, should be able to set new file
        $result = $service
            ->file($filePath)
            ->transcribe();

        $this->assertInstanceOf(TranscriptionResult::class, $result);
    }

    /** @test */
    public function transcription_result_can_be_converted_to_array(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('audio.wav');

        $result = ElevenLabs::stt()
            ->file($filePath)
            ->transcribe();

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('text', $array);
        $this->assertArrayHasKey('words', $array);
        $this->assertArrayHasKey('confidence', $array);
    }
}

