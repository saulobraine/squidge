<?php
// infrastructure/WordPress/Admin/DashboardController.php

namespace Squidge\Infrastructure\WordPress\Admin;

use Squidge\Services\Contracts\StatisticsAggregatorInterface;
use Squidge\Services\Contracts\BatchOptimizationInterface;
use Squidge\Infrastructure\WordPress\Assets\AdminScriptsEnqueuer;
use Squidge\Infrastructure\WordPress\Assets\AdminStylesEnqueuer;

class DashboardController
{
  private StatisticsAggregatorInterface $statisticsAggregator;
  private BatchOptimizationInterface $batchOptimizer;
  private AdminScriptsEnqueuer $scriptsEnqueuer;
  private AdminStylesEnqueuer $stylesEnqueuer;

  public function __construct(
    StatisticsAggregatorInterface $statisticsAggregator,
    BatchOptimizationInterface $batchOptimizer,
    AdminScriptsEnqueuer $scriptsEnqueuer,
    AdminStylesEnqueuer $stylesEnqueuer
  ) {
    $this->statisticsAggregator = $statisticsAggregator;
    $this->batchOptimizer = $batchOptimizer;
    $this->scriptsEnqueuer = $scriptsEnqueuer;
    $this->stylesEnqueuer = $stylesEnqueuer;

    $this->registerHooks();
  }

  private function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addDashboardMenu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
  }

  public function addDashboardMenu(): void
  {
    add_menu_page(
      'Squidge Dashboard',
      'Squidge',
      'manage_options',
      'squidge-dashboard',
      [$this, 'renderDashboardPage'],
      'dashicons-images-alt2',
      30
    );

    add_submenu_page(
      'squidge-dashboard',
      'Dashboard',
      'Dashboard',
      'manage_options',
      'squidge-dashboard',
      [$this, 'renderDashboardPage']
    );

    add_submenu_page(
      'squidge-dashboard',
      'Statistics',
      'Statistics',
      'manage_options',
      'squidge-statistics',
      [$this, 'renderStatisticsPage']
    );

    add_submenu_page(
      'squidge-dashboard',
      'Batch Optimizer',
      'Batch Optimizer',
      'manage_options',
      'squidge-batch-optimizer',
      [$this, 'renderBatchOptimizerPage']
    );
  }

  public function enqueueAssets(string $hook): void
  {
    if (strpos($hook, 'squidge') === false) {
      return;
    }

    $this->scriptsEnqueuer->enqueueScripts();
    $this->stylesEnqueuer->enqueueStyles();
  }

  public function renderDashboardPage(): void
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $overviewStats = $this->statisticsAggregator->getOverviewStatistics();
    $recentOptimizations = $this->statisticsAggregator->getRecentOptimizations(5);
    $activeJobs = $this->batchOptimizer->getBatchJobHistory();

    include SQUIDGE_TEMPLATE_PATH . '/admin/dashboard.php';
  }

  public function renderStatisticsPage(): void
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $overviewStats = $this->statisticsAggregator->getOverviewStatistics();
    $toolStats = $this->statisticsAggregator->getToolSpecificStatistics();
    $recentOptimizations = $this->statisticsAggregator->getRecentOptimizations(10);

    include SQUIDGE_TEMPLATE_PATH . '/admin/statistics.php';
  }

  public function renderBatchOptimizerPage(): void
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $availableImages = $this->batchOptimizer->getAvailableImages();
    $activeJobs = $this->batchOptimizer->getBatchJobHistory();

    include SQUIDGE_TEMPLATE_PATH . '/admin/batch-optimizer.php';
  }
}
