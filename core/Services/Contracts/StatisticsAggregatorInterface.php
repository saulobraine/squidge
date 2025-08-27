<?php
// core/Services/Contracts/StatisticsAggregatorInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\StatisticsSummary;

interface StatisticsAggregatorInterface
{
  public function getOverviewStatistics(): StatisticsSummary;
  public function getToolSpecificStatistics(): array;
  public function getRecentOptimizations(int $limit = 10): array;
  public function getOptimizationTrends(string $period = 'month'): array;
  public function getTopOptimizedImages(int $limit = 10): array;
  public function getOptimizationEfficiency(): array;
}
