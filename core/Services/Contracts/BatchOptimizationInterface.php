<?php
// core/Services/Contracts/BatchOptimizationInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\BatchJob;
use Squidge\ValueObjects\BatchConfiguration;

interface BatchOptimizationInterface
{
  public function startBatchJob(BatchConfiguration $config): BatchJob;
  public function processBatch(BatchJob $job): void;
  public function pauseBatchJob(BatchJob $job): void;
  public function resumeBatchJob(BatchJob $job): void;
  public function cancelBatchJob(BatchJob $job): void;
  public function getBatchJobStatus(string $jobId): array;
  public function getAvailableImages(): array;
  public function getBatchJobHistory(): array;
}
