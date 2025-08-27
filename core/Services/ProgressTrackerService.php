<?php
// core/Services/ProgressTrackerService.php

namespace Squidge\Services;

use Squidge\Services\Contracts\ProgressTrackerInterface;
use Squidge\ValueObjects\OptimizationProgress;

class ProgressTrackerService implements ProgressTrackerInterface
{
  private array $activeProgress = [];

  public function trackProgress(string $jobId, OptimizationProgress $progress): void
  {
    $this->activeProgress[$jobId] = $progress;
  }

  public function getProgress(string $jobId): ?OptimizationProgress
  {
    return $this->activeProgress[$jobId] ?? null;
  }

  public function updateProgress(string $jobId, int $processed, int $successful, int $failed, int $savedBytes): void
  {
    $progress = $this->getProgress($jobId);
    if ($progress) {
      $progress->updateProgress($processed, $successful, $failed, $savedBytes);
    }
  }

  public function completeJob(string $jobId): void
  {
    $progress = $this->getProgress($jobId);
    if ($progress) {
      $progress->setStatus('completed');
    }
  }

  public function getActiveJobs(): array
  {
    return array_map(function ($progress) {
      return $progress->toArray();
    }, $this->activeProgress);
  }

  public function removeJob(string $jobId): void
  {
    unset($this->activeProgress[$jobId]);
  }

  public function getJobCount(): int
  {
    return count($this->activeProgress);
  }

  public function getJobsByStatus(string $status): array
  {
    return array_filter($this->activeProgress, function ($progress) use ($status) {
      return $progress->getCurrentStatus() === $status;
    });
  }
}
