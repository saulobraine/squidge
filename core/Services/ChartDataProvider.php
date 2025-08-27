<?php
// core/Services/ChartDataProvider.php

namespace Squidge\Services;

use Squidge\Services\Contracts\ChartDataProviderInterface;
use Squidge\ValueObjects\ChartPeriod;
use Squidge\Database\Contracts\StatisticsRepositoryInterface;

class ChartDataProvider implements ChartDataProviderInterface
{
  private StatisticsRepositoryInterface $repository;

  public function __construct(StatisticsRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }

  public function getOptimizationHistoryChart(ChartPeriod $period): array
  {
    $dateRange = $period->getDateRange();
    $historyData = $this->repository->getOptimizationHistoryByPeriod($dateRange);

    $labels = [];
    $datasets = [
      'total_optimizations' => [],
      'successful_optimizations' => [],
      'failed_optimizations' => []
    ];

    foreach ($historyData as $date => $data) {
      $labels[] = $date;
      $datasets['total_optimizations'][] = $data['total'] ?? 0;
      $datasets['successful_optimizations'][] = $data['successful'] ?? 0;
      $datasets['failed_optimizations'][] = $data['failed'] ?? 0;
    }

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'label' => 'Total de Otimizações',
          'data' => $datasets['total_optimizations'],
          'borderColor' => '#667eea',
          'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
          'tension' => 0.4
        ],
        [
          'label' => 'Sucessos',
          'data' => $datasets['successful_optimizations'],
          'borderColor' => '#10b981',
          'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
          'tension' => 0.4
        ],
        [
          'label' => 'Falhas',
          'data' => $datasets['failed_optimizations'],
          'borderColor' => '#ef4444',
          'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
          'tension' => 0.4
        ]
      ]
    ];
  }

  public function getToolPerformanceChart(): array
  {
    $toolStats = $this->repository->getToolPerformanceStatistics();

    $labels = [];
    $datasets = [
      'total_optimizations' => [],
      'average_reduction' => [],
      'success_rate' => []
    ];

    foreach ($toolStats as $tool => $stats) {
      $labels[] = ucfirst($tool);
      $datasets['total_optimizations'][] = $stats['total'] ?? 0;
      $datasets['average_reduction'][] = $stats['average_reduction'] ?? 0;
      $datasets['success_rate'][] = $stats['success_rate'] ?? 0;
    }

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'label' => 'Total de Otimizações',
          'data' => $datasets['total_optimizations'],
          'backgroundColor' => [
            'rgba(102, 126, 234, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)'
          ],
          'borderWidth' => 2,
          'borderColor' => '#ffffff'
        ]
      ]
    ];
  }

  public function getFileTypeDistributionChart(): array
  {
    $fileTypeStats = $this->repository->getFileTypeDistribution();

    $labels = [];
    $data = [];
    $backgroundColor = [
      'rgba(102, 126, 234, 0.8)',
      'rgba(16, 185, 129, 0.8)',
      'rgba(245, 158, 11, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(139, 92, 246, 0.8)'
    ];

    $i = 0;
    foreach ($fileTypeStats as $fileType => $count) {
      $labels[] = strtoupper($fileType);
      $data[] = $count;
      $i++;
    }

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'data' => $data,
          'backgroundColor' => array_slice($backgroundColor, 0, count($labels)),
          'borderWidth' => 2,
          'borderColor' => '#ffffff'
        ]
      ]
    ];
  }

  public function getSavingsTrendChart(ChartPeriod $period): array
  {
    $dateRange = $period->getDateRange();
    $savingsData = $this->repository->getSavingsTrendByPeriod($dateRange);

    $labels = [];
    $savingsMB = [];
    $cumulativeSavings = [];

    $totalSavings = 0;
    foreach ($savingsData as $date => $data) {
      $labels[] = $date;
      $dailySavings = ($data['total_saved_bytes'] ?? 0) / 1024 / 1024;
      $savingsMB[] = round($dailySavings, 2);
      $totalSavings += $dailySavings;
      $cumulativeSavings[] = round($totalSavings, 2);
    }

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'label' => 'Economia Diária (MB)',
          'data' => $savingsMB,
          'borderColor' => '#10b981',
          'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
          'yAxisID' => 'y',
          'tension' => 0.4
        ],
        [
          'label' => 'Economia Acumulada (MB)',
          'data' => $cumulativeSavings,
          'borderColor' => '#667eea',
          'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
          'yAxisID' => 'y1',
          'tension' => 0.4
        ]
      ]
    ];
  }

  public function getOptimizationStatusChart(): array
  {
    $statusStats = $this->repository->getOptimizationStatusDistribution();

    $labels = ['Sucesso', 'Falha', 'Pendente'];
    $data = [
      $statusStats['success'] ?? 0,
      $statusStats['error'] ?? 0,
      $statusStats['pending'] ?? 0
    ];

    $backgroundColor = [
      'rgba(16, 185, 129, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(245, 158, 11, 0.8)'
    ];

    return [
      'labels' => $labels,
      'datasets' => [
        [
          'data' => $data,
          'backgroundColor' => $backgroundColor,
          'borderWidth' => 2,
          'borderColor' => '#ffffff'
        ]
      ]
    ];
  }
}
