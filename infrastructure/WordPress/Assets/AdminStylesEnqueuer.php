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
      SQUIDGE_ASSETS_URL . '/admin/admin.css',
      [],
      $this->pluginVersion
    );

    wp_enqueue_style(
      'squidge-batch-optimizer',
      SQUIDGE_ASSETS_URL . '/admin/batch-optimizer.css',
      ['squidge-admin'],
      $this->pluginVersion
    );
  }
}
