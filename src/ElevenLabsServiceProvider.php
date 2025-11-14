<?php

namespace DigitalCoreHub\LaravelElevenLabs;

use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\SttEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\TtsEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\VoicesEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Services\STT\SttService;
use DigitalCoreHub\LaravelElevenLabs\Services\TTS\TtsService;
use DigitalCoreHub\LaravelElevenLabs\Services\Voices\VoiceService;
use Illuminate\Support\ServiceProvider;

class ElevenLabsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/elevenlabs.php',
            'elevenlabs'
        );

        $this->app->singleton(ElevenLabsClient::class, function ($app) {
            $config = $app['config']['elevenlabs'];

            return new ElevenLabsClient(
                apiKey: $config['api_key'],
                baseUrl: $config['base_url'],
                timeout: $config['timeout']
            );
        });

        $this->app->singleton(TtsEndpoint::class, function ($app) {
            return new TtsEndpoint(
                $app->make(ElevenLabsClient::class)
            );
        });

        $this->app->bind(TtsService::class, function ($app) {
            $config = $app['config']['elevenlabs'];

            return new TtsService(
                endpoint: $app->make(TtsEndpoint::class),
                defaultVoice: $config['default_voice'],
                defaultFormat: $config['default_format']
            );
        });

        $this->app->singleton(SttEndpoint::class, function ($app) {
            return new SttEndpoint(
                $app->make(ElevenLabsClient::class)
            );
        });

        $this->app->bind(SttService::class, function ($app) {
            return new SttService(
                endpoint: $app->make(SttEndpoint::class)
            );
        });

        $this->app->singleton(VoicesEndpoint::class, function ($app) {
            return new VoicesEndpoint(
                $app->make(ElevenLabsClient::class)
            );
        });

        $this->app->bind(VoiceService::class, function ($app) {
            return new VoiceService(
                endpoint: $app->make(VoicesEndpoint::class)
            );
        });

        $this->app->singleton('elevenlabs', function ($app) {
            return $app;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/elevenlabs.php' => config_path('elevenlabs.php'),
        ], 'elevenlabs-config');
    }
}
