<?php

namespace DigitalCoreHub\LaravelElevenLabs\Services\TTS;

use DigitalCoreHub\LaravelElevenLabs\Data\AudioFile;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\TtsEndpoint;

class TtsService
{
    protected ?string $voice = null;

    protected ?string $text = null;

    protected ?string $format = null;

    protected ?array $voiceSettings = null;

    /**
     * Create a new TtsService instance.
     */
    public function __construct(
        protected TtsEndpoint $endpoint,
        protected string $defaultVoice,
        protected string $defaultFormat
    ) {}

    /**
     * Set the voice ID to use.
     */
    public function voice(string $voiceId): self
    {
        $this->voice = $voiceId;

        return $this;
    }

    /**
     * Set the text to convert to speech.
     */
    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set the audio format.
     */
    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Set voice settings (stability, similarity_boost, etc.).
     */
    public function voiceSettings(array $settings): self
    {
        $this->voiceSettings = $settings;

        return $this;
    }

    /**
     * Generate the audio and return an AudioFile instance.
     */
    public function generate(): AudioFile
    {
        if (empty($this->text)) {
            throw new \InvalidArgumentException('Text is required for TTS generation.');
        }

        $voice = $this->voice ?? $this->defaultVoice;
        $format = $this->format ?? $this->defaultFormat;

        $audioContent = $this->endpoint->generate(
            $this->text,
            $voice,
            $format,
            $this->voiceSettings
        );

        return new AudioFile(
            content: $audioContent,
            format: $format,
            filename: $this->generateFilename($format)
        );
    }

    /**
     * Generate the audio and save it to storage.
     */
    public function save(string $path, ?string $disk = null): bool
    {
        $audioFile = $this->generate();

        return $audioFile->save($path, $disk);
    }

    /**
     * Generate a filename based on the format.
     */
    protected function generateFilename(string $format): string
    {
        $extension = match (true) {
            str_starts_with($format, 'mp3') => 'mp3',
            str_starts_with($format, 'pcm') => 'pcm',
            str_starts_with($format, 'ulaw') => 'ulaw',
            default => 'mp3',
        };

        return 'tts_'.time().'.'.$extension;
    }

    /**
     * Reset the service state.
     */
    public function reset(): self
    {
        $this->voice = null;
        $this->text = null;
        $this->format = null;
        $this->voiceSettings = null;

        return $this;
    }
}
