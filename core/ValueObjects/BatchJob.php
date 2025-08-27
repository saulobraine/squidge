<?php
// core/ValueObjects/BatchJob.php

namespace Squidge\ValueObjects;

use Squidge\ValueObjects\BatchConfiguration;

class BatchJob
{
  private string $id;
  private string $status;
  private BatchConfiguration $configuration;
  private \DateTime $createdAt;
  private ?\DateTime $startedAt;
  private ?\DateTime $completedAt;
  private ?\DateTime $pausedAt;
  private int $totalImages;
  private int $processedImages;
  private int $successfulOptimizations;
  private int $failedOptimizations;
  private int $totalSavedBytes;
  private ?string $errorMessage;

  public const STATUS_PENDING = 'pending';
  public const STATUS_RUNNING = 'running';
  public const STATUS_PAUSED = 'paused';
  public const STATUS_COMPLETED = 'completed';
  public const STATUS_CANCELLED = 'cancelled';
  public const STATUS_ERROR = 'error';

  public function __construct(BatchConfiguration $configuration)
  {
    $this->id = $this->generateUniqueId();
    $this->status = self::STATUS_PENDING;
    $this->configuration = $configuration;
    $this->createdAt = new \DateTime();
    $this->startedAt = null;
    $this->completedAt = null;
    $this->pausedAt = null;
    $this->totalImages = count($configuration->getImageIds());
    $this->processedImages = 0;
    $this->successfulOptimizations = 0;
    $this->failedOptimizations = 0;
    $this->totalSavedBytes = 0;
    $this->errorMessage = null;
  }

  public function start(): void
  {
    if ($this->status !== self::STATUS_PENDING && $this->status !== self::STATUS_PAUSED) {
      throw new \InvalidArgumentException('Job cannot be started from current status: ' . $this->status);
    }

    $this->status = self::STATUS_RUNNING;
    $this->startedAt = new \DateTime();
    $this->pausedAt = null;
  }

  public function pause(): void
  {
    if ($this->status !== self::STATUS_RUNNING) {
      throw new \InvalidArgumentException('Job cannot be paused from current status: ' . $this->status);
    }

    $this->status = self::STATUS_PAUSED;
    $this->pausedAt = new \DateTime();
  }

  public function resume(): void
  {
    if ($this->status !== self::STATUS_PAUSED) {
      throw new \InvalidArgumentException('Job cannot be resumed from current status: ' . $this->status);
    }

    $this->status = self::STATUS_RUNNING;
    $this->pausedAt = null;
  }

  public function complete(): void
  {
    if ($this->status !== self::STATUS_RUNNING) {
      throw new \InvalidArgumentException('Job cannot be completed from current status: ' . $this->status);
    }

    $this->status = self::STATUS_COMPLETED;
    $this->completedAt = new \DateTime();
  }

  public function cancel(): void
  {
    if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_ERROR])) {
      throw new \InvalidArgumentException('Job cannot be cancelled from current status: ' . $this->status);
    }

    $this->status = self::STATUS_CANCELLED;
    $this->completedAt = new \DateTime();
  }

  public function markAsError(string $errorMessage): void
  {
    $this->status = self::STATUS_ERROR;
    $this->errorMessage = $errorMessage;
    $this->completedAt = new \DateTime();
  }

  public function updateProgress(int $processed, int $successful, int $failed, int $savedBytes): void
  {
    $this->processedImages = $processed;
    $this->successfulOptimizations = $successful;
    $this->failedOptimizations = $failed;
    $this->totalSavedBytes = $savedBytes;
  }

  public function getProgressPercentage(): float
  {
    if ($this->totalImages === 0) {
      return 0.0;
    }

    return round(($this->processedImages / $this->totalImages) * 100, 2);
  }

  public function getAverageReduction(): float
  {
    if ($this->successfulOptimizations === 0) {
      return 0.0;
    }

    $totalOriginalSize = $this->configuration->getTotalOriginalSize();
    if ($totalOriginalSize === 0) {
      return 0.0;
    }

    return round(($this->totalSavedBytes / $totalOriginalSize) * 100, 2);
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'status' => $this->status,
      'total_images' => $this->totalImages,
      'processed_images' => $this->processedImages,
      'successful_optimizations' => $this->successfulOptimizations,
      'failed_optimizations' => $this->failedOptimizations,
      'total_saved_bytes' => $this->totalSavedBytes,
      'progress_percentage' => $this->getProgressPercentage(),
      'average_reduction' => $this->getAverageReduction(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
      'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
      'paused_at' => $this->pausedAt?->format('Y-m-d H:i:s'),
      'error_message' => $this->errorMessage,
    ];
  }

  private function generateUniqueId(): string
  {
    return uniqid('batch_', true);
  }

  // Getters
  public function getId(): string
  {
    return $this->id;
  }
  public function getStatus(): string
  {
    return $this->status;
  }
  public function getConfiguration(): BatchConfiguration
  {
    return $this->configuration;
  }
  public function getCreatedAt(): \DateTime
  {
    return $this->createdAt;
  }
  public function getStartedAt(): ?\DateTime
  {
    return $this->startedAt;
  }
  public function getCompletedAt(): ?\DateTime
  {
    return $this->completedAt;
  }
  public function getPausedAt(): ?\DateTime
  {
    return $this->pausedAt;
  }
  public function getTotalImages(): int
  {
    return $this->totalImages;
  }
  public function getProcessedImages(): int
  {
    return $this->processedImages;
  }
  public function getSuccessfulOptimizations(): int
  {
    return $this->successfulOptimizations;
  }
  public function getFailedOptimizations(): int
  {
    return $this->failedOptimizations;
  }
  public function getTotalSavedBytes(): int
  {
    return $this->totalSavedBytes;
  }
  public function getErrorMessage(): ?string
  {
    return $this->errorMessage;
  }
}
