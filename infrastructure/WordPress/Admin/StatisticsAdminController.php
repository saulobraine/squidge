<?php
// infrastructure/WordPress/Admin/StatisticsAdminController.php

namespace Squidge\Infrastructure\WordPress\Admin;

use Squidge\Services\Contracts\StatisticsAggregatorInterface;
use Squidge\Services\Contracts\ChartDataProviderInterface;
use Squidge\Services\Contracts\OptimizationTrackingInterface;

class StatisticsAdminController
{
  private StatisticsAggregatorInterface $statisticsAggregator;
  private ChartDataProviderInterface $chartDataProvider;
  private OptimizationTrackingInterface $trackingService;

  public function __construct(
    StatisticsAggregatorInterface $statisticsAggregator,
    ChartDataProviderInterface $chartDataProvider,
    OptimizationTrackingInterface $trackingService
  ) {
    $this->statisticsAggregator = $statisticsAggregator;
    $this->chartDataProvider = $chartDataProvider;
    $this->trackingService = $trackingService;

    $this->registerHooks();
  }

  private function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addStatisticsMenu']);
    add_action('wp_ajax_squidge_get_statistics', [$this, 'handleAjaxRequest']);
    add_action('wp_ajax_squidge_get_chart_data', [$this, 'handleChartDataRequest']);
  }

  public function addStatisticsMenu(): void
  {
    add_submenu_page(
      'tools.php',
      'Squidge Statistics',
      'Squidge Stats',
      'manage_options',
      'squidge-statistics',
      [$this, 'renderStatisticsPage']
    );
  }

  public function renderStatisticsPage(): void
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $overviewStats = $this->statisticsAggregator->getOverviewStatistics();
    $toolStats = $this->statisticsAggregator->getToolSpecificStatistics();
    $recentOptimizations = $this->statisticsAggregator->getRecentOptimizations();
    $failedOptimizations = $this->trackingService->getFailedOptimizations();

    include SQUIDGE_TEMPLATE_PATH . '/admin/statistics.php';
  }

  public function handleAjaxRequest(): void
  {
    check_ajax_referer('squidge_statistics_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('Insufficient permissions');
    }

    $action = sanitize_text_field($_POST['action_type'] ?? '');

    try {
      switch ($action) {
        case 'overview':
          $data = $this->getOverviewData();
          break;
        case 'tool_stats':
          $data = $this->getToolStatsData();
          break;
        case 'recent_optimizations':
          $data = $this->getRecentOptimizationsData();
          break;
        case 'failed_optimizations':
          $data = $this->getFailedOptimizationsData();
          break;
        default:
          throw new \InvalidArgumentException('Invalid action type');
      }

      wp_send_json_success($data);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function handleChartDataRequest(): void
  {
    check_ajax_referer('squidge_statistics_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('Insufficient permissions');
    }

    $chartType = sanitize_text_field($_POST['chart_type'] ?? '');
    $period = sanitize_text_field($_POST['period'] ?? 'month');

    try {
      $data = $this->getChartData($chartType, $period);
      wp_send_json_success($data);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  private function getOverviewData(): array
  {
    $overview = $this->statisticsAggregator->getOverviewStatistics();
    $efficiency = $this->statisticsAggregator->getOptimizationEfficiency();

    return [
      'overview' => $overview->toArray(),
      'efficiency' => $efficiency
    ];
  }

  private function getToolStatsData(): array
  {
    return $this->statisticsAggregator->getToolSpecificStatistics();
  }

  private function getRecentOptimizationsData(): array
  {
    $limit = (int) ($_POST['limit'] ?? 10);
    return $this->statisticsAggregator->getRecentOptimizations($limit);
  }

  private function getFailedOptimizationsData(): array
  {
    return $this->trackingService->getFailedOptimizations();
  }

  private function getChartData(string $chartType, string $period): array
  {
    $chartPeriod = new \Squidge\ValueObjects\ChartPeriod($period);

    switch ($chartType) {
      case 'optimization_history':
        return $this->chartDataProvider->getOptimizationHistoryChart($chartPeriod);
      case 'tool_performance':
        return $this->chartDataProvider->getToolPerformanceChart();
      case 'file_type_distribution':
        return $this->chartDataProvider->getFileTypeDistributionChart();
      case 'savings_trend':
        return $this->chartDataProvider->getSavingsTrendChart($chartPeriod);
      case 'optimization_status':
        return $this->chartDataProvider->getOptimizationStatusChart();
      default:
        throw new \InvalidArgumentException('Invalid chart type');
    }
  }
}
