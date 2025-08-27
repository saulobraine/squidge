<?php
// infrastructure/WordPress/Assets/AdminStylesEnqueuer.php

namespace Squidge\Infrastructure\WordPress\Assets;

class AdminStylesEnqueuer
{
  private string $pluginUrl;
  private string $pluginVersion;

  public function __construct(string $pluginUrl, string $pluginVersion)
  {
    $this->pluginUrl = $pluginUrl;
    $this->pluginVersion = $pluginVersion;
  }

  public function enqueueStyles(): void
  {
    wp_enqueue_style(
      'squidge-admin',
      $this->pluginUrl . '/assets/admin/admin.css',
      [],
      $this->pluginVersion
    );

    wp_enqueue_style(
      'squidge-batch-optimizer',
      $this->pluginUrl . '/assets/admin/batch-optimizer.css',
      ['squidge-admin'],
      $this->pluginVersion
    );
  }
}
