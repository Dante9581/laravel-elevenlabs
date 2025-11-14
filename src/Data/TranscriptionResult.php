<?php

namespace DigitalCoreHub\LaravelElevenLabs\Data;

class TranscriptionResult
{
    /**
     * Create a new TranscriptionResult instance.
     */
    public function __construct(
        public readonly string $text,
        public readonly ?array $words = null,
        public readonly ?float $confidence = null
    ) {}

    /**
     * Get the transcribed text.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the words array if available.
     */
    public function getWords(): ?array
    {
        return $this->words;
    }

    /**
     * Get the confidence score if available.
     */
    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'words' => $this->words,
            'confidence' => $this->confidence,
        ];
    }
}
