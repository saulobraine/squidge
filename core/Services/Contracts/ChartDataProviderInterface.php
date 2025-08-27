<?php
// core/Services/Contracts/ChartDataProviderInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\ChartPeriod;

interface ChartDataProviderInterface
{
  public function getOptimizationHistoryChart(ChartPeriod $period): array;
  public function getToolPerformanceChart(): array;
  public function getFileTypeDistributionChart(): array;
  public function getSavingsTrendChart(ChartPeriod $period): array;
  public function getOptimizationStatusChart(): array;
}
