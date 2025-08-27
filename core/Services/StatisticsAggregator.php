<?php
// core/Services/StatisticsAggregator.php

namespace Squidge\Services;

use Squidge\Services\Contracts\StatisticsAggregatorInterface;
use Squidge\Database\Contracts\StatisticsRepositoryInterface;
use Squidge\ValueObjects\StatisticsSummary;

class StatisticsAggregator implements StatisticsAggregatorInterface
{
  private StatisticsRepositoryInterface $repository;

  public function __construct(StatisticsRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function getOverviewStatistics(): StatisticsSummary
  {
    $basicStats = $this->repository->getAggregatedStats();
    $toolStats = $this->getToolSpecificStatistics();

    $enhancedStats = array_merge($basicStats, [
      'successful_optimizations' => $this->countSuccessfulOptimizations(),
      'failed_optimizations' => $this->countFailedOptimizations(),
      'last_optimization' => $this->getLastOptimizationDate(),
      'tool_breakdown' => $toolStats
    ]);

    return new StatisticsSummary($enhancedStats);
  }

  public function getToolSpecificStatistics(): array
  {
    $tools = ['jpegoptim', 'optipng', 'cwebp', 'avifenc'];
    $toolStats = [];

    foreach ($tools as $tool) {
      $toolStats[$tool] = $this->getToolStats($tool);
    }

    return $toolStats;
  }

  public function getRecentOptimizations(int $limit = 10): array
  {
    $recentStats = $this->repository->getRecentOptimizations($limit);

    return array_map(function ($stat) {
      return [
        'id' => $stat->getId(),
        'attachment_id' => $stat->getAttachmentId(),
        'file_path' => $stat->getFilePath(),
        'tool_used' => $stat->getToolUsed(),
        'saved_percentage' => $stat->getSavedPercentage(),
        'status' => $stat->getStatus(),
        'optimization_date' => $stat->getOptimizationDate()->format('Y-m-d H:i:s'),
        'file_name' => basename($stat->getFilePath())
      ];
    }, $recentStats);
  }

  public function getOptimizationTrends(string $period = 'month'): array
  {
    $trends = [];
    $days = $this->getDaysForPeriod($period);

    for ($i = $days; $i >= 0; $i--) {
      $date = date('Y-m-d', strtotime("-{$i} days"));
      $trends[$date] = $this->getDailyOptimizations($date);
    }

    return $trends;
  }

  public function getTopOptimizedImages(int $limit = 10): array
  {
    $topImages = $this->repository->getTopOptimizedImages($limit);

    return array_map(function ($image) {
      return [
        'attachment_id' => $image['attachment_id'],
        'file_path' => $image['file_path'],
        'total_saved_bytes' => $image['total_saved_bytes'],
        'average_reduction' => $image['average_reduction'],
        'optimization_count' => $image['optimization_count'],
        'file_name' => basename($image['file_path'])
      ];
    }, $topImages);
  }

  public function getOptimizationEfficiency(): array
  {
    $overview = $this->getOverviewStatistics();

    return [
      'overall_efficiency' => $overview->getOptimizationEfficiency(),
      'performance_grade' => $overview->getPerformanceGrade(),
      'success_rate' => $overview->getSuccessRate(),
      'average_reduction' => $overview->getAverageReduction(),
      'total_saved_mb' => $overview->getTotalSavedMB(),
      'total_saved_gb' => $overview->getTotalSavedGB(),
    ];
  }

  private function countSuccessfulOptimizations(): int
  {
    return $this->repository->countOptimizationsByStatus('success');
  }

  private function countFailedOptimizations(): int
  {
    return $this->repository->countOptimizationsByStatus('error');
  }

  private function getLastOptimizationDate(): string
  {
    $lastOptimization = $this->repository->getLastOptimizationDate();
    return $lastOptimization ?: date('Y-m-d H:i:s');
  }

  private function getToolStats(string $tool): array
  {
    $toolStats = $this->repository->getToolStatistics($tool);

    return [
      'total_optimizations' => $toolStats['total'] ?? 0,
      'successful_optimizations' => $toolStats['successful'] ?? 0,
      'average_reduction' => $toolStats['average_reduction'] ?? 0.0,
      'total_saved_bytes' => $toolStats['total_saved_bytes'] ?? 0,
    ];
  }

  private function getDaysForPeriod(string $period): int
  {
    $periods = [
      'day' => 1,
      'week' => 7,
      'month' => 30,
      'quarter' => 90,
      'year' => 365
    ];

    return $periods[$period] ?? 30;
  }

  private function getDailyOptimizations(string $date): array
  {
    $dailyStats = $this->repository->getDailyOptimizations($date);

    return [
      'total_optimizations' => $dailyStats['total'] ?? 0,
      'successful_optimizations' => $dailyStats['successful'] ?? 0,
      'total_saved_bytes' => $dailyStats['total_saved_bytes'] ?? 0,
      'average_reduction' => $dailyStats['average_reduction'] ?? 0.0,
    ];
  }
}
