<?php

namespace DigitalCoreHub\LaravelElevenLabs\Tests\Fake;

use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\VoicesEndpoint;

class FakeVoicesProvider extends VoicesEndpoint
{
    /**
     * Get fake voices list.
     */
    public function list(): array
    {
        return [
            'voices' => [
                [
                    'voice_id' => 'fake-voice-1',
                    'name' => 'Fake Voice 1',
                    'samples' => [],
                    'category' => 'premade',
                    'description' => 'A fake voice for testing',
                ],
                [
                    'voice_id' => 'fake-voice-2',
                    'name' => 'Fake Voice 2',
                    'samples' => [],
                    'category' => 'premade',
                    'description' => 'Another fake voice for testing',
                ],
            ],
        ];
    }

    /**
     * Get fake voice by ID.
     */
    public function get(string $voiceId): array
    {
        return [
            'voice_id' => $voiceId,
            'name' => 'Fake Voice',
            'samples' => [],
            'category' => 'premade',
            'description' => 'A fake voice for testing',
            'settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ];
    }

    /**
     * Create fake voice.
     */
    public function create(string $name, array $files, ?string $description = null, ?array $labels = null): array
    {
        return [
            'voice_id' => 'new-fake-voice-'.time(),
            'name' => $name,
            'samples' => [],
            'category' => 'custom',
            'description' => $description ?? 'A custom fake voice',
            'labels' => $labels ?? [],
        ];
    }

    /**
     * Delete fake voice.
     */
    public function delete(string $voiceId): bool
    {
        return true;
    }
}

