<?php
// core/Services/StatisticsService.php

namespace Squidge\Services;

use Squidge\Database\Contracts\StatisticsRepositoryInterface;
use Squidge\Database\Entities\OptimizationStatistics;
use Squidge\Services\OptimizationTracker;

class StatisticsService
{
  private StatisticsRepositoryInterface $repository;
  private OptimizationTracker $tracker;

  public function __construct(
    StatisticsRepositoryInterface $repository,
    OptimizationTracker $tracker
  ) {
    $this->repository = $repository;
    $this->tracker = $tracker;
  }

  public function trackOptimization(
    int $attachmentId,
    string $filePath,
    int $originalSize,
    string $toolUsed,
    string $mimeType
  ): OptimizationStatistics {
    $optimizedSize = $this->tracker->simulateOptimization($filePath, $originalSize);

    $statistics = new OptimizationStatistics(
      $attachmentId,
      $originalSize,
      $optimizedSize,
      $toolUsed,
      $filePath,
      $mimeType
    );

    $this->repository->save($statistics);

    return $statistics;
  }

  public function getOptimizationStats(int $attachmentId): array
  {
    $statistics = $this->repository->findByAttachmentId($attachmentId);

    return [
      'total_optimizations' => count($statistics),
      'successful_optimizations' => count(array_filter($statistics, fn($s) => $s->isSuccessful())),
      'total_saved_bytes' => array_sum(array_map(fn($s) => $s->getSavedBytes(), $statistics)),
      'average_reduction' => $this->calculateAverageReduction($statistics),
    ];
  }

  public function getGlobalStats(): array
  {
    return $this->repository->getAggregatedStats();
  }

  private function calculateAverageReduction(array $statistics): float
  {
    if (empty($statistics)) {
      return 0.0;
    }

    $totalReduction = array_sum(array_map(fn($s) => $s->getSavedPercentage(), $statistics));
    return round($totalReduction / count($statistics), 2);
  }
}
