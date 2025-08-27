<?php
// core/Services/OptimizationTrackingService.php

namespace Squidge\Services;

use Squidge\Services\Contracts\OptimizationTrackingInterface;
use Squidge\ValueObjects\OptimizationResult;
use Squidge\Database\Contracts\StatisticsRepositoryInterface;

class OptimizationTrackingService implements OptimizationTrackingInterface
{
  private StatisticsRepositoryInterface $repository;

  public function __construct(StatisticsRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function trackOptimizationResult(OptimizationResult $result): bool
  {
    try {
      $data = $result->toArray();
      $data['attachment_id'] = $result->getAttachmentId();

      return $this->repository->saveOptimizationResult($data);
    } catch (\Exception $e) {
      error_log("Failed to track optimization result: " . $e->getMessage());
      return false;
    }
  }

  public function getOptimizationHistory(int $attachmentId): array
  {
    $history = $this->repository->findByAttachmentId($attachmentId);

    return array_map(function ($stat) {
      return [
        'id' => $stat->getId(),
        'tool_used' => $stat->getToolUsed(),
        'original_size' => $stat->getOriginalSize(),
        'optimized_size' => $stat->getOptimizedSize(),
        'saved_bytes' => $stat->getSavedBytes(),
        'saved_percentage' => $stat->getSavedPercentage(),
        'status' => $stat->getStatus(),
        'optimization_date' => $stat->getOptimizationDate()->format('Y-m-d H:i:s'),
        'error_message' => $stat->getErrorMessage()
      ];
    }, $history);
  }

  public function getOptimizationMetrics(int $attachmentId): array
  {
    $history = $this->getOptimizationHistory($attachmentId);

    if (empty($history)) {
      return [
        'total_optimizations' => 0,
        'successful_optimizations' => 0,
        'failed_optimizations' => 0,
        'total_saved_bytes' => 0,
        'average_reduction' => 0.0,
        'best_optimization' => null,
        'last_optimization' => null
      ];
    }

    $successful = array_filter($history, fn($h) => $h['status'] === 'success');
    $failed = array_filter($history, fn($h) => $h['status'] === 'error');

    $totalSavedBytes = array_sum(array_column($history, 'saved_bytes'));
    $averageReduction = count($successful) > 0
      ? array_sum(array_column($successful, 'saved_percentage')) / count($successful)
      : 0.0;

    $bestOptimization = $this->findBestOptimization($history);
    $lastOptimization = $this->findLastOptimization($history);

    return [
      'total_optimizations' => count($history),
      'successful_optimizations' => count($successful),
      'failed_optimizations' => count($failed),
      'total_saved_bytes' => $totalSavedBytes,
      'average_reduction' => round($averageReduction, 2),
      'best_optimization' => $bestOptimization,
      'last_optimization' => $lastOptimization
    ];
  }

  public function updateOptimizationStatus(int $optimizationId, string $status): bool
  {
    try {
      return $this->repository->updateOptimizationStatus($optimizationId, $status);
    } catch (\Exception $e) {
      error_log("Failed to update optimization status: " . $e->getMessage());
      return false;
    }
  }

  public function getFailedOptimizations(): array
  {
    $failedStats = $this->repository->findByStatus('error');

    return array_map(function ($stat) {
      return [
        'id' => $stat->getId(),
        'attachment_id' => $stat->getAttachmentId(),
        'file_path' => $stat->getFilePath(),
        'tool_used' => $stat->getToolUsed(),
        'error_message' => $stat->getErrorMessage(),
        'optimization_date' => $stat->getOptimizationDate()->format('Y-m-d H:i:s'),
        'file_name' => basename($stat->getFilePath())
      ];
    }, $failedStats);
  }

  private function findBestOptimization(array $history): ?array
  {
    if (empty($history)) {
      return null;
    }

    $successful = array_filter($history, fn($h) => $h['status'] === 'success');

    if (empty($successful)) {
      return null;
    }

    $best = array_reduce($successful, function ($carry, $item) {
      if ($carry === null || $item['saved_percentage'] > $carry['saved_percentage']) {
        return $item;
      }
      return $carry;
    });

    return $best;
  }

  private function findLastOptimization(array $history): ?array
  {
    if (empty($history)) {
      return null;
    }

    $last = array_reduce($history, function ($carry, $item) {
      if ($carry === null) {
        return $item;
      }

      $carryDate = new \DateTime($carry['optimization_date']);
      $itemDate = new \DateTime($item['optimization_date']);

      return $itemDate > $carryDate ? $item : $carry;
    });

    return $last;
  }
}
