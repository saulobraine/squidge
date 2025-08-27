<?php
// core/Services/Contracts/ProgressTrackerInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\OptimizationProgress;

interface ProgressTrackerInterface
{
  public function trackProgress(string $jobId, OptimizationProgress $progress): void;
  public function getProgress(string $jobId): ?OptimizationProgress;
  public function updateProgress(string $jobId, int $processed, int $successful, int $failed, int $savedBytes): void;
  public function completeJob(string $jobId): void;
  public function getActiveJobs(): array;
}
