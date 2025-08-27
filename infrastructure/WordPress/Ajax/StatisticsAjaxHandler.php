<?php
// infrastructure/WordPress/Ajax/StatisticsAjaxHandler.php

namespace Squidge\Infrastructure\WordPress\Ajax;

use Squidge\Services\Contracts\StatisticsAggregatorInterface;
use Squidge\Services\Contracts\ChartDataProviderInterface;
use Squidge\Services\Contracts\OptimizationTrackingInterface;

class StatisticsAjaxHandler
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

    $this->registerAjaxHandlers();
  }

  private function registerAjaxHandlers(): void
  {
    add_action('wp_ajax_squidge_get_overview_stats', [$this, 'getOverviewStats']);
    add_action('wp_ajax_squidge_get_tool_stats', [$this, 'getToolStats']);
    add_action('wp_ajax_squidge_get_recent_optimizations', [$this, 'getRecentOptimizations']);
    add_action('wp_ajax_squidge_get_failed_optimizations', [$this, 'getFailedOptimizations']);
    add_action('wp_ajax_squidge_get_optimization_history', [$this, 'getOptimizationHistory']);
    add_action('wp_ajax_squidge_get_chart_data', [$this, 'getChartData']);
    add_action('wp_ajax_squidge_update_optimization_status', [$this, 'updateOptimizationStatus']);
  }

  public function getOverviewStats(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $overview = $this->statisticsAggregator->getOverviewStatistics();
      $efficiency = $this->statisticsAggregator->getOptimizationEfficiency();

      wp_send_json_success([
        'overview' => $overview->toArray(),
        'efficiency' => $efficiency
      ]);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function getToolStats(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $toolStats = $this->statisticsAggregator->getToolSpecificStatistics();
      wp_send_json_success($toolStats);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function getRecentOptimizations(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $limit = (int) ($_POST['limit'] ?? 10);
      $recent = $this->statisticsAggregator->getRecentOptimizations($limit);
      wp_send_json_success($recent);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function getFailedOptimizations(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $failed = $this->trackingService->getFailedOptimizations();
      wp_send_json_success($failed);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function getOptimizationHistory(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $attachmentId = (int) ($_POST['attachment_id'] ?? 0);

      if ($attachmentId <= 0) {
        throw new \InvalidArgumentException('Invalid attachment ID');
      }

      $history = $this->trackingService->getOptimizationHistory($attachmentId);
      $metrics = $this->trackingService->getOptimizationMetrics($attachmentId);

      wp_send_json_success([
        'history' => $history,
        'metrics' => $metrics
      ]);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function getChartData(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $chartType = sanitize_text_field($_POST['chart_type'] ?? '');
      $period = sanitize_text_field($_POST['period'] ?? 'month');

      if (empty($chartType)) {
        throw new \InvalidArgumentException('Chart type is required');
      }

      $chartPeriod = new \Squidge\ValueObjects\ChartPeriod($period);
      $data = $this->getChartDataByType($chartType, $chartPeriod);

      wp_send_json_success($data);
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  public function updateOptimizationStatus(): void
  {
    $this->verifyNonce('squidge_statistics_nonce');
    $this->verifyCapability('manage_options');

    try {
      $optimizationId = (int) ($_POST['optimization_id'] ?? 0);
      $status = sanitize_text_field($_POST['status'] ?? '');

      if ($optimizationId <= 0) {
        throw new \InvalidArgumentException('Invalid optimization ID');
      }

      if (empty($status)) {
        throw new \InvalidArgumentException('Status is required');
      }

      $validStatuses = ['pending', 'success', 'error'];
      if (!in_array($status, $validStatuses)) {
        throw new \InvalidArgumentException('Invalid status');
      }

      $result = $this->trackingService->updateOptimizationStatus($optimizationId, $status);

      if ($result) {
        wp_send_json_success('Status updated successfully');
      } else {
        wp_send_json_error('Failed to update status');
      }
    } catch (\Exception $e) {
      wp_send_json_error($e->getMessage());
    }
  }

  private function getChartDataByType(string $chartType, \Squidge\ValueObjects\ChartPeriod $period): array
  {
    switch ($chartType) {
      case 'optimization_history':
        return $this->chartDataProvider->getOptimizationHistoryChart($period);
      case 'tool_performance':
        return $this->chartDataProvider->getToolPerformanceChart();
      case 'file_type_distribution':
        return $this->chartDataProvider->getFileTypeDistributionChart();
      case 'savings_trend':
        return $this->chartDataProvider->getSavingsTrendChart($period);
      case 'optimization_status':
        return $this->chartDataProvider->getOptimizationStatusChart();
      default:
        throw new \InvalidArgumentException('Invalid chart type: ' . $chartType);
    }
  }

  private function verifyNonce(string $nonceKey): void
  {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', $nonceKey)) {
      wp_send_json_error('Invalid nonce');
    }
  }

  private function verifyCapability(string $capability): void
  {
    if (!current_user_can($capability)) {
      wp_send_json_error('Insufficient permissions');
    }
  }
}
