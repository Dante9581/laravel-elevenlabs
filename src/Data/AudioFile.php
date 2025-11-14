<?php

namespace DigitalCoreHub\LaravelElevenLabs\Data;

class AudioFile
{
    /**
     * Create a new AudioFile instance.
     */
    public function __construct(
        public readonly string $content,
        public readonly string $format,
        public readonly ?string $filename = null
    ) {}

    /**
     * Get the audio content as a string.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get the audio format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Get the suggested filename.
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Save the audio file to storage.
     */
    public function save(string $path, ?string $disk = null): bool
    {
        $storage = app('filesystem')->disk($disk);

        return $storage->put($path, $this->content);
    }
}
