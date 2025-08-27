<?php
// core/ValueObjects/BatchConfiguration.php

namespace Squidge\ValueObjects;

class BatchConfiguration
{
  private array $imageIds;
  private array $optimizationSettings;
  private int $batchSize;
  private bool $generateWebP;
  private bool $generateAVIF;
  private bool $overwriteOriginals;
  private string $priority;
  private int $totalOriginalSize;

  public const PRIORITY_LOW = 'low';
  public const PRIORITY_NORMAL = 'normal';
  public const PRIORITY_HIGH = 'high';

  public function __construct(
    array $imageIds,
    array $optimizationSettings,
    int $batchSize = 10,
    bool $generateWebP = true,
    bool $generateAVIF = true,
    bool $overwriteOriginals = false,
    string $priority = self::PRIORITY_NORMAL
  ) {
    $this->validateImageIds($imageIds);
    $this->validateOptimizationSettings($optimizationSettings);
    $this->validateBatchSize($batchSize);
    $this->validatePriority($priority);

    $this->imageIds = $imageIds;
    $this->optimizationSettings = $optimizationSettings;
    $this->batchSize = $batchSize;
    $this->generateWebP = $generateWebP;
    $this->generateAVIF = $generateAVIF;
    $this->overwriteOriginals = $overwriteOriginals;
    $this->priority = $priority;
    $this->totalOriginalSize = $this->calculateTotalOriginalSize();
  }

  private function validateImageIds(array $imageIds): void
  {
    if (empty($imageIds)) {
      throw new \InvalidArgumentException('Image IDs cannot be empty');
    }

    foreach ($imageIds as $id) {
      if (!is_numeric($id) || $id <= 0) {
        throw new \InvalidArgumentException('Invalid image ID: ' . $id);
      }
    }
  }

  private function validateOptimizationSettings(array $settings): void
  {
    $requiredKeys = ['jpg_quality', 'png_optimization', 'webp_quality', 'avif_quality'];

    foreach ($requiredKeys as $key) {
      if (!isset($settings[$key])) {
        throw new \InvalidArgumentException("Missing required setting: {$key}");
      }
    }

    if ($settings['jpg_quality'] < 1 || $settings['jpg_quality'] > 100) {
      throw new \InvalidArgumentException('JPG quality must be between 1 and 100');
    }

    if ($settings['webp_quality'] < 1 || $settings['webp_quality'] > 100) {
      throw new \InvalidArgumentException('WebP quality must be between 1 and 100');
    }

    if ($settings['avif_quality'] < 1 || $settings['avif_quality'] > 100) {
      throw new \InvalidArgumentException('AVIF quality must be between 1 and 100');
    }

    $validPngOptimizations = ['o1', 'o2', 'o3', 'o4', 'o5'];
    if (!in_array($settings['png_optimization'], $validPngOptimizations)) {
      throw new \InvalidArgumentException('Invalid PNG optimization level');
    }
  }

  private function validateBatchSize(int $batchSize): void
  {
    if ($batchSize < 1 || $batchSize > 100) {
      throw new \InvalidArgumentException('Batch size must be between 1 and 100');
    }
  }

  private function validatePriority(string $priority): void
  {
    $validPriorities = [self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH];
    if (!in_array($priority, $validPriorities)) {
      throw new \InvalidArgumentException('Invalid priority level');
    }
  }

  private function calculateTotalOriginalSize(): int
  {
    // Para simplicidade, retornar um valor simulado
    // Em produção, isso seria calculado com base no tamanho real dos arquivos
    return count($this->imageIds) * 1000000; // 1MB por imagem
  }

  public function getEstimatedProcessingTime(): int
  {
    $totalImages = count($this->imageIds);
    $baseTimePerImage = 2; // segundos base por imagem

    $multiplier = match ($this->priority) {
      self::PRIORITY_LOW => 1.5,
      self::PRIORITY_NORMAL => 1.0,
      self::PRIORITY_HIGH => 0.7,
      default => 1.0
    };

    return (int) ($totalImages * $baseTimePerImage * $multiplier);
  }

  public function getFormattedTotalSize(): string
  {
    if ($this->totalOriginalSize < 1024) {
      return $this->totalOriginalSize . ' B';
    }

    if ($this->totalOriginalSize < 1024 * 1024) {
      return round($this->totalOriginalSize / 1024, 2) . ' KB';
    }

    if ($this->totalOriginalSize < 1024 * 1024 * 1024) {
      return round($this->totalOriginalSize / (1024 * 1024), 2) . ' MB';
    }

    return round($this->totalOriginalSize / (1024 * 1024 * 1024), 2) . ' GB';
  }

  public function toArray(): array
  {
    return [
      'image_ids' => $this->imageIds,
      'optimization_settings' => $this->optimizationSettings,
      'batch_size' => $this->batchSize,
      'generate_webp' => $this->generateWebP,
      'generate_avif' => $this->generateAVIF,
      'overwrite_originals' => $this->overwriteOriginals,
      'priority' => $this->priority,
      'total_original_size' => $this->totalOriginalSize,
      'formatted_total_size' => $this->getFormattedTotalSize(),
      'estimated_processing_time' => $this->getEstimatedProcessingTime(),
    ];
  }

  // Getters
  public function getImageIds(): array
  {
    return $this->imageIds;
  }
  public function getOptimizationSettings(): array
  {
    return $this->optimizationSettings;
  }
  public function getBatchSize(): int
  {
    return $this->batchSize;
  }
  public function getGenerateWebP(): bool
  {
    return $this->generateWebP;
  }
  public function getGenerateAVIF(): bool
  {
    return $this->generateAVIF;
  }
  public function getOverwriteOriginals(): bool
  {
    return $this->overwriteOriginals;
  }
  public function getPriority(): string
  {
    return $this->priority;
  }
  public function getTotalOriginalSize(): int
  {
    return $this->totalOriginalSize;
  }
}
