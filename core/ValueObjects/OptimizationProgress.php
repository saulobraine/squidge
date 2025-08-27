<?php
// core/ValueObjects/OptimizationProgress.php

namespace Squidge\ValueObjects;

class OptimizationProgress
{
  private string $jobId;
  private int $totalImages;
  private int $processedImages;
  private int $successfulOptimizations;
  private int $failedOptimizations;
  private int $totalSavedBytes;
  private float $progressPercentage;
  private float $averageReduction;
  private \DateTime $lastUpdated;
  private string $currentStatus;

  public function __construct(string $jobId, int $totalImages)
  {
    $this->jobId = $jobId;
    $this->totalImages = $totalImages;
    $this->processedImages = 0;
    $this->successfulOptimizations = 0;
    $this->failedOptimizations = 0;
    $this->totalSavedBytes = 0;
    $this->progressPercentage = 0.0;
    $this->averageReduction = 0.0;
    $this->lastUpdated = new \DateTime();
    $this->currentStatus = 'initialized';
  }

  public function updateProgress(int $processed, int $successful, int $failed, int $savedBytes): void
  {
    $this->processedImages = $processed;
    $this->successfulOptimizations = $successful;
    $this->failedOptimizations = $failed;
    $this->totalSavedBytes = $savedBytes;
    $this->lastUpdated = new \DateTime();

    $this->calculateMetrics();
  }

  public function incrementProcessed(int $count = 1): void
  {
    $this->processedImages += $count;
    $this->lastUpdated = new \DateTime();
    $this->calculateMetrics();
  }

  public function incrementSuccessful(int $count = 1): void
  {
    $this->successfulOptimizations += $count;
    $this->lastUpdated = new \DateTime();
    $this->calculateMetrics();
  }

  public function incrementFailed(int $count = 1): void
  {
    $this->failedOptimizations += $count;
    $this->lastUpdated = new \DateTime();
    $this->calculateMetrics();
  }

  public function addSavedBytes(int $bytes): void
  {
    $this->totalSavedBytes += $bytes;
    $this->lastUpdated = new \DateTime();
    $this->calculateMetrics();
  }

  public function setStatus(string $status): void
  {
    $this->currentStatus = $status;
    $this->lastUpdated = new \DateTime();
  }

  private function calculateMetrics(): void
  {
    $this->progressPercentage = $this->totalImages > 0
      ? round(($this->processedImages / $this->totalImages) * 100, 2)
      : 0.0;

    $this->averageReduction = $this->successfulOptimizations > 0
      ? round(($this->totalSavedBytes / max($this->totalSavedBytes, 1)) * 100, 2)
      : 0.0;
  }

  public function isComplete(): bool
  {
    return $this->processedImages >= $this->totalImages;
  }

  public function getRemainingImages(): int
  {
    return max(0, $this->totalImages - $this->processedImages);
  }

  public function getFormattedSavedSize(): string
  {
    if ($this->totalSavedBytes < 1024) {
      return $this->totalSavedBytes . ' B';
    }

    if ($this->totalSavedBytes < 1024 * 1024) {
      return round($this->totalSavedBytes / 1024, 2) . ' KB';
    }

    if ($this->totalSavedBytes < 1024 * 1024 * 1024) {
      return round($this->totalSavedBytes / (1024 * 1024), 2) . ' MB';
    }

    return round($this->totalSavedBytes / (1024 * 1024 * 1024), 2) . ' GB';
  }

  public function getEstimatedTimeRemaining(): int
  {
    if ($this->processedImages === 0) {
      return 0;
    }

    $elapsedTime = time() - $this->lastUpdated->getTimestamp();
    $timePerImage = $elapsedTime / $this->processedImages;
    $remainingImages = $this->getRemainingImages();

    return (int) ($timePerImage * $remainingImages);
  }

  public function toArray(): array
  {
    return [
      'job_id' => $this->jobId,
      'total_images' => $this->totalImages,
      'processed_images' => $this->processedImages,
      'successful_optimizations' => $this->successfulOptimizations,
      'failed_optimizations' => $this->failedOptimizations,
      'total_saved_bytes' => $this->totalSavedBytes,
      'progress_percentage' => $this->progressPercentage,
      'average_reduction' => $this->averageReduction,
      'current_status' => $this->currentStatus,
      'last_updated' => $this->lastUpdated->format('Y-m-d H:i:s'),
      'remaining_images' => $this->getRemainingImages(),
      'formatted_saved_size' => $this->getFormattedSavedSize(),
      'estimated_time_remaining' => $this->getEstimatedTimeRemaining(),
      'is_complete' => $this->isComplete(),
    ];
  }

  // Getters
  public function getJobId(): string
  {
    return $this->jobId;
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
  public function getProgressPercentage(): float
  {
    return $this->progressPercentage;
  }
  public function getAverageReduction(): float
  {
    return $this->averageReduction;
  }
  public function getCurrentStatus(): string
  {
    return $this->currentStatus;
  }
  public function getLastUpdated(): \DateTime
  {
    return $this->lastUpdated;
  }
}
