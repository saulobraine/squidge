<?php
// infrastructure/WordPress/Hooks/ActivationHooks.php

namespace Squidge\Infrastructure\WordPress\Hooks;

use Squidge\Database\Migrations\CreateStatisticsTable;
use Squidge\Infrastructure\WordPress\Database\WordPressDatabaseAdapter;

class ActivationHooks
{
  private CreateStatisticsTable $migration;

  public function __construct(CreateStatisticsTable $migration)
  {
    $this->migration = $migration;
  }

  public function activate(): void
  {
    try {
      $this->migration->up();
      $this->setDefaultOptions();
      flush_rewrite_rules();
    } catch (\Exception $e) {
      error_log("Squidge activation failed: " . $e->getMessage());
      throw $e;
    }
  }

  public function deactivate(): void
  {
    try {
      wp_clear_scheduled_hook('squidge_cleanup_old_stats');
      flush_rewrite_rules();
    } catch (\Exception $e) {
      error_log("Squidge deactivation failed: " . $e->getMessage());
    }
  }

  private function setDefaultOptions(): void
  {
    $defaults = [
      'squidge_jpg_enable' => true,
      'squidge_jpg_quality' => 80,
      'squidge_png_enable' => true,
      'squidge_png_quality' => 'o3',
      'squidge_webp_enable' => true,
      'squidge_webp_quality' => 80,
      'squidge_avif_enable' => true,
      'squidge_batch_limit' => 100,
      'squidge_auto_optimize' => true
    ];

    foreach ($defaults as $option => $value) {
      if (get_option($option) === false) {
        add_option($option, $value);
      }
    }
  }
}
