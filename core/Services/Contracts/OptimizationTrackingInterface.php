<?php
// core/Services/Contracts/OptimizationTrackingInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\OptimizationResult;

interface OptimizationTrackingInterface
{
  public function trackOptimizationResult(OptimizationResult $result): bool;
  public function getOptimizationHistory(int $attachmentId): array;
  public function getOptimizationMetrics(int $attachmentId): array;
  public function updateOptimizationStatus(int $optimizationId, string $status): bool;
  public function getFailedOptimizations(): array;
}
