<?php
// infrastructure/WordPress/Assets/AdminScriptsEnqueuer.php

namespace Squidge\Infrastructure\WordPress\Assets;

class AdminScriptsEnqueuer
{
  private string $pluginUrl;
  private string $pluginVersion;

  public function __construct(string $pluginUrl, string $pluginVersion)
  {
    $this->pluginUrl = $pluginUrl;
    $this->pluginVersion = $pluginVersion;
  }

  public function enqueueScripts(): void
  {
    wp_enqueue_script(
      'squidge-admin',
      $this->pluginUrl . '/assets/admin/admin.js',
      ['jquery', 'wp-util'],
      $this->pluginVersion,
      true
    );

    wp_enqueue_script(
      'squidge-chart',
      'https://cdn.jsdelivr.net/npm/chart.js',
      [],
      '3.9.1',
      true
    );

    wp_enqueue_script(
      'squidge-batch-optimizer',
      $this->pluginUrl . '/assets/admin/batch-optimizer.js',
      ['jquery', 'squidge-admin'],
      $this->pluginVersion,
      true
    );

    $this->localizeScripts();
  }

  private function localizeScripts(): void
  {
    wp_localize_script('squidge-admin', 'squidgeAdmin', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('squidge_dashboard_nonce'),
      'strings' => [
        'confirmCancel' => 'Are you sure you want to cancel this batch job?',
        'confirmDelete' => 'Are you sure you want to delete this item?',
        'loading' => 'Loading...',
        'error' => 'An error occurred',
        'success' => 'Operation completed successfully',
      ],
    ]);

    wp_localize_script('squidge-batch-optimizer', 'squidgeBatch', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('squidge_batch_nonce'),
      'strings' => [
        'confirmCancel' => 'Are you sure you want to cancel this batch job?',
        'confirmPause' => 'Are you sure you want to pause this batch job?',
        'confirmResume' => 'Are you sure you want to resume this batch job?',
        'loading' => 'Processing...',
        'error' => 'An error occurred during optimization',
        'success' => 'Optimization completed successfully',
      ],
    ]);
  }
}
