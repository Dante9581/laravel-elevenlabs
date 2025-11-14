<?php

namespace DigitalCoreHub\LaravelElevenLabs\Data;

class Voice
{
    /**
     * Create a new Voice instance.
     */
    public function __construct(
        public readonly string $voiceId,
        public readonly string $name,
        public readonly array $samples = [],
        public readonly ?string $category = null,
        public readonly ?array $fineTuning = null,
        public readonly ?array $labels = null,
        public readonly ?string $description = null,
        public readonly ?string $previewUrl = null,
        public readonly ?array $settings = null,
        public readonly ?array $sharing = null,
        public readonly ?array $highQualityBaseModelIds = null,
        public readonly ?string $safetyControlTier = null,
        public readonly ?int $permissionOnResource = null,
        public readonly ?array $createdAt = null
    ) {}

    /**
     * Create a Voice instance from API response.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            voiceId: $data['voice_id'] ?? $data['id'] ?? '',
            name: $data['name'] ?? '',
            samples: $data['samples'] ?? [],
            category: $data['category'] ?? null,
            fineTuning: $data['fine_tuning'] ?? null,
            labels: $data['labels'] ?? null,
            description: $data['description'] ?? null,
            previewUrl: $data['preview_url'] ?? null,
            settings: $data['settings'] ?? null,
            sharing: $data['sharing'] ?? null,
            highQualityBaseModelIds: $data['high_quality_base_model_ids'] ?? null,
            safetyControlTier: $data['safety_control_tier'] ?? null,
            permissionOnResource: $data['permission_on_resource'] ?? null,
            createdAt: $data['created_at'] ?? null
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'voice_id' => $this->voiceId,
            'name' => $this->name,
            'samples' => $this->samples,
            'category' => $this->category,
            'fine_tuning' => $this->fineTuning,
            'labels' => $this->labels,
            'description' => $this->description,
            'preview_url' => $this->previewUrl,
            'settings' => $this->settings,
            'sharing' => $this->sharing,
            'high_quality_base_model_ids' => $this->highQualityBaseModelIds,
            'safety_control_tier' => $this->safetyControlTier,
            'permission_on_resource' => $this->permissionOnResource,
            'created_at' => $this->createdAt,
        ];
    }
}

