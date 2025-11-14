<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ElevenLabs API Key
    |--------------------------------------------------------------------------
    |
    | Your ElevenLabs API key. You can get this from your ElevenLabs dashboard.
    | You can also set this via the ELEVENLABS_API_KEY environment variable.
    |
    */

    'api_key' => env('ELEVENLABS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the ElevenLabs API.
    |
    */

    'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Voice
    |--------------------------------------------------------------------------
    |
    | The default voice ID to use when no voice is specified.
    | Common voices: 'nova', 'rachel', 'domi', 'bella', 'antoni', 'elli', 'josh', 'arnold', 'adam', 'sam'
    |
    */

    'default_voice' => env('ELEVENLABS_DEFAULT_VOICE', 'nova'),

    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | The default audio format for TTS output.
    | Supported formats: 'mp3_44100_128', 'mp3_44100_192', 'mp3_44100_256', 'pcm_16000', 'pcm_22050', 'pcm_24000', 'pcm_44100', 'ulaw_8000'
    |
    */

    'default_format' => env('ELEVENLABS_DEFAULT_FORMAT', 'mp3_44100_128'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */

    'timeout' => env('ELEVENLABS_TIMEOUT', 30),
];
