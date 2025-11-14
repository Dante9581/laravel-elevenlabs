<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Feature;

use DigitalCoreHub\LaravelElevenLabs\Data\Voice;
use DigitalCoreHub\LaravelElevenLabs\Data\VoiceCollection;
use DigitalCoreHub\LaravelElevenLabs\Events\VoiceCreated;
use DigitalCoreHub\LaravelElevenLabs\Events\VoiceSynced;
use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;
use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\VoicesEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Jobs\SyncVoicesJob;
use DigitalCoreHub\LaravelElevenLabs\Tests\Fake\FakeVoicesProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class VoicesTest extends TestCase
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
     * Replace VoicesEndpoint with FakeVoicesProvider for testing.
     */
    protected function useFakeProvider(): void
    {
        $this->app->singleton(VoicesEndpoint::class, function ($app) {
            $client = $app->make(ElevenLabsClient::class);
            return new FakeVoicesProvider($client);
        });
    }

    /**
     * Create a test audio file.
     */
    protected function createTestAudioFile(string $path = 'test-voice.wav'): string
    {
        Storage::disk('local')->put($path, 'fake audio content');
        return Storage::disk('local')->path($path);
    }

    /** @test */
    public function it_can_list_voices_with_fake_provider(): void
    {
        $this->useFakeProvider();

        $voices = ElevenLabs::voices()->list();

        $this->assertInstanceOf(VoiceCollection::class, $voices);
        $this->assertGreaterThan(0, $voices->count());
        $this->assertInstanceOf(Voice::class, $voices->first());
    }

    /** @test */
    public function it_can_get_single_voice(): void
    {
        $this->useFakeProvider();

        $voice = ElevenLabs::voices()->get('fake-voice-1');

        $this->assertInstanceOf(Voice::class, $voice);
        $this->assertEquals('fake-voice-1', $voice->voiceId);
        $this->assertEquals('Fake Voice', $voice->name);
    }

    /** @test */
    public function it_can_create_custom_voice_with_fluent_api(): void
    {
        $this->useFakeProvider();
        Event::fake();

        $filePath = $this->createTestAudioFile('voice1.wav');

        $voice = ElevenLabs::voices()
            ->name('My Custom Voice')
            ->files([$filePath])
            ->description('A custom voice for testing')
            ->create();

        $this->assertInstanceOf(Voice::class, $voice);
        $this->assertEquals('My Custom Voice', $voice->name);
        $this->assertEquals('custom', $voice->category);

        Event::assertDispatched(VoiceCreated::class, function ($event) use ($voice) {
            return $event->voice->voiceId === $voice->voiceId;
        });
    }

    /** @test */
    public function it_can_create_voice_with_storage_disk_files(): void
    {
        $this->useFakeProvider();

        Storage::disk('local')->put('voices/voice1.wav', 'fake audio content');

        $voice = ElevenLabs::voices()
            ->name('My Custom Voice')
            ->files([['path' => 'voices/voice1.wav', 'disk' => 'local']])
            ->create();

        $this->assertInstanceOf(Voice::class, $voice);
        $this->assertEquals('My Custom Voice', $voice->name);
    }

    /** @test */
    public function it_can_create_voice_with_labels(): void
    {
        $this->useFakeProvider();

        $filePath = $this->createTestAudioFile('voice1.wav');

        $voice = ElevenLabs::voices()
            ->name('My Custom Voice')
            ->files([$filePath])
            ->labels(['accent' => 'british', 'age' => 'young'])
            ->create();

        $this->assertInstanceOf(Voice::class, $voice);
    }

    /** @test */
    public function it_throws_exception_when_name_is_missing(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Voice name is required.');

        ElevenLabs::voices()
            ->files(['voice1.wav'])
            ->create();
    }

    /** @test */
    public function it_throws_exception_when_files_are_missing(): void
    {
        $this->useFakeProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one file is required to create a voice.');

        ElevenLabs::voices()
            ->name('My Custom Voice')
            ->create();
    }

    /** @test */
    public function it_can_sync_voices(): void
    {
        $this->useFakeProvider();
        Event::fake();

        $voices = ElevenLabs::voices()->sync();

        $this->assertInstanceOf(VoiceCollection::class, $voices);
        $this->assertGreaterThan(0, $voices->count());

        Event::assertDispatched(VoiceSynced::class, function ($event) use ($voices) {
            return $event->voices->count() === $voices->count();
        });
    }

    /** @test */
    public function it_can_delete_voice(): void
    {
        $this->useFakeProvider();

        $deleted = ElevenLabs::voices()->delete('fake-voice-1');

        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_use_sync_voices_job(): void
    {
        $this->useFakeProvider();
        Queue::fake();
        Event::fake();

        SyncVoicesJob::dispatch();

        Queue::assertPushed(SyncVoicesJob::class);
    }

    /** @test */
    public function voice_collection_can_find_by_id(): void
    {
        $this->useFakeProvider();

        $voices = ElevenLabs::voices()->list();
        $voice = $voices->findById('fake-voice-1');

        $this->assertInstanceOf(Voice::class, $voice);
        $this->assertEquals('fake-voice-1', $voice->voiceId);
    }

    /** @test */
    public function voice_collection_can_find_by_name(): void
    {
        $this->useFakeProvider();

        $voices = ElevenLabs::voices()->list();
        $found = $voices->findByName('Fake Voice 1');

        $this->assertInstanceOf(VoiceCollection::class, $found);
        $this->assertGreaterThan(0, $found->count());
    }

    /** @test */
    public function it_can_reset_service_state(): void
    {
        $this->useFakeProvider();

        $service = ElevenLabs::voices()
            ->name('Test Voice')
            ->files(['test.wav'])
            ->description('Test description');

        $service->reset();

        $filePath = $this->createTestAudioFile('voice1.wav');

        $voice = $service
            ->name('New Voice')
            ->files([$filePath])
            ->create();

        $this->assertInstanceOf(Voice::class, $voice);
        $this->assertEquals('New Voice', $voice->name);
    }

    /** @test */
    public function voice_can_be_converted_to_array(): void
    {
        $this->useFakeProvider();

        $voice = ElevenLabs::voices()->get('fake-voice-1');
        $array = $voice->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('voice_id', $array);
        $this->assertArrayHasKey('name', $array);
    }
}

