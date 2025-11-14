<?php

namespace DigitalCoreHub\LaravelElevenLabs\Data;

use Illuminate\Support\Collection;

class VoiceCollection extends Collection
{
    /**
     * Create a new VoiceCollection instance.
     */
    public function __construct($items = [])
    {
        $voices = array_map(function ($item) {
            return $item instanceof Voice ? $item : Voice::fromArray($item);
        }, $items);

        parent::__construct($voices);
    }

    /**
     * Create a VoiceCollection from API response.
     */
    public static function fromArray(array $data): self
    {
        $voices = array_map(fn ($voice) => Voice::fromArray($voice), $data['voices'] ?? $data);

        return new self($voices);
    }

    /**
     * Find a voice by ID.
     */
    public function findById(string $voiceId): ?Voice
    {
        return $this->first(fn (Voice $voice) => $voice->voiceId === $voiceId);
    }

    /**
     * Find voices by name.
     */
    public function findByName(string $name): self
    {
        return $this->filter(fn (Voice $voice) => $voice->name === $name)->values();
    }
}

