<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Feature;

use DigitalCoreHub\LaravelElevenLabs\Data\AudioFile;
use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;
use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\TtsEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Tests\Fake\FakeTtsProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class TtsTest extends TestCase
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
     * Replace TtsEndpoint with FakeTtsProvider for testing.
     */
    protected function useFakeProvider(): void
    {
        $this->app->singleton(TtsEndpoint::class, function ($app) {
            $client = $app->make(ElevenLabsClient::class);

            return new FakeTtsProvider($client);
        });
    }

    /** @test */
    public function it_can_read_config_values(): void
    {
        $this->assertEquals('test-api-key', Config::get('elevenlabs.api_key'));
        $this->assertEquals('nova', Config::get('elevenlabs.default_voice'));
        $this->assertEquals('mp3_44100_128', Config::get('elevenlabs.default_format'));
    }

    /** @test */
    public function it_can_use_fluent_api_with_fake_provider(): void
    {
        $this->useFakeProvider();

        $audioFile = ElevenLabs::tts()
            ->voice('nova')
            ->text('Hello from Laravel')
            ->format('mp3_44100_128')
            ->generate();

        $this->assertInstanceOf(AudioFile::class, $audioFile);
        $this->assertNotEmpty($audioFile->getContent());
        $this->assertEquals('mp3_44100_128', $audioFile->getFormat());
    }

    /** @test */
    public function it_can_use_default_voice_and_format(): void
    {
        $this->useFakeProvider();

        $audioFile = ElevenLabs::tts()
            ->text('Hello from Laravel')
            ->generate();

        $this->assertInstanceOf(AudioFile::class, $audioFile);
        $this->assertNotEmpty($audioFile->getContent());
    }

    /** @test */
    public function it_can_save_audio_file_to_storage(): void
    {
        $this->useFakeProvider();

        $saved = ElevenLabs::tts()
            ->voice('nova')
            ->text('Hello from Laravel')
            ->format('mp3_44100_128')
            ->save('voices/hello.mp3', 'local');

        $this->assertTrue($saved);
        Storage::disk('local')->assertExists('voices/hello.mp3');
    }

    /** @test */
    public function it_throws_exception_when_text_is_missing(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Text is required for TTS generation.');

        ElevenLabs::tts()
            ->voice('nova')
            ->generate();
    }

    /** @test */
    public function it_can_chain_voice_settings(): void
    {
        $this->useFakeProvider();

        $audioFile = ElevenLabs::tts()
            ->voice('nova')
            ->text('Hello from Laravel')
            ->voiceSettings([
                'stability' => 0.7,
                'similarity_boost' => 0.8,
            ])
            ->generate();

        $this->assertInstanceOf(AudioFile::class, $audioFile);
    }

    /** @test */
    public function it_can_reset_service_state(): void
    {
        $this->useFakeProvider();

        $service = ElevenLabs::tts()
            ->voice('nova')
            ->text('First text')
            ->format('mp3_44100_128');

        $service->reset();

        // After reset, should use defaults
        $audioFile = $service
            ->text('Second text')
            ->generate();

        $this->assertInstanceOf(AudioFile::class, $audioFile);
    }
}
