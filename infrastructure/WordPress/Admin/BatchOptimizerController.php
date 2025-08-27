<?php
// infrastructure/WordPress/Admin/BatchOptimizerController.php

namespace Squidge\Infrastructure\WordPress\Admin;

use Squidge\Services\Contracts\BatchOptimizationInterface;
use Squidge\Services\Contracts\ProgressTrackerInterface;
use Squidge\ValueObjects\BatchConfiguration;

class BatchOptimizerController
{
  private BatchOptimizationInterface $batchOptimizer;
  private ProgressTrackerInterface $progressTracker;

  public function __construct(
    BatchOptimizationInterface $batchOptimizer,
    ProgressTrackerInterface $progressTracker
  ) {
    $this->batchOptimizer = $batchOptimizer;
    $this->progressTracker = $progressTracker;

    $this->registerHooks();
  }

  private function registerHooks(): void
  {
    add_action('wp_ajax_start_batch_optimization', [$this, 'handleStartBatch']);
    add_action('wp_ajax_get_batch_progress', [$this, 'handleGetProgress']);
    add_action('wp_ajax_pause_batch', [$this, 'handlePauseBatch']);
    add_action('wp_ajax_resume_batch', [$this, 'handleResumeBatch']);
    add_action('wp_ajax_cancel_batch', [$this, 'handleCancelBatch']);
  }

  public function handleStartBatch(): void
  {
    check_ajax_referer('squidge_batch_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    try {
      $imageIds = array_map('intval', $_POST['image_ids'] ?? []);
      $optimizationSettings = $this->sanitizeOptimizationSettings($_POST['optimization_settings'] ?? []);
      $batchSize = (int) ($_POST['batch_size'] ?? 10);
      $generateWebP = (bool) ($_POST['generate_webp'] ?? true);
      $generateAVIF = (bool) ($_POST['generate_avif'] ?? true);
      $overwriteOriginals = (bool) ($_POST['overwrite_originals'] ?? false);
      $priority = sanitize_text_field($_POST['priority'] ?? 'normal');

      if (empty($imageIds)) {
        throw new \InvalidArgumentException('No images selected for optimization');
      }

      $config = new BatchConfiguration(
        $imageIds,
        $optimizationSettings,
        $batchSize,
        $generateWebP,
        $generateAVIF,
        $overwriteOriginals,
        $priority
      );

      $job = $this->batchOptimizer->startBatchJob($config);

      wp_send_json_success([
        'job_id' => $job->getId(),
        'message' => 'Batch optimization started successfully',
      ]);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  public function handleGetProgress(): void
  {
    check_ajax_referer('squidge_batch_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    try {
      $jobId = sanitize_text_field($_POST['job_id'] ?? '');

      if (empty($jobId)) {
        throw new \InvalidArgumentException('Job ID is required');
      }

      $status = $this->batchOptimizer->getBatchJobStatus($jobId);
      wp_send_json_success($status);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  public function handlePauseBatch(): void
  {
    check_ajax_referer('squidge_batch_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    try {
      $jobId = sanitize_text_field($_POST['job_id'] ?? '');

      if (empty($jobId)) {
        throw new \InvalidArgumentException('Job ID is required');
      }

      $job = $this->getJobById($jobId);
      $this->batchOptimizer->pauseBatchJob($job);

      wp_send_json_success(['message' => 'Batch job paused successfully']);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  public function handleResumeBatch(): void
  {
    check_ajax_referer('squidge_batch_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    try {
      $jobId = sanitize_text_field($_POST['job_id'] ?? '');

      if (empty($jobId)) {
        throw new \InvalidArgumentException('Job ID is required');
      }

      $job = $this->getJobById($jobId);
      $this->batchOptimizer->resumeBatchJob($job);

      wp_send_json_success(['message' => 'Batch job resumed successfully']);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  public function handleCancelBatch(): void
  {
    check_ajax_referer('squidge_batch_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    try {
      $jobId = sanitize_text_field($_POST['job_id'] ?? '');

      if (empty($jobId)) {
        throw new \InvalidArgumentException('Job ID is required');
      }

      $job = $this->getJobById($jobId);
      $this->batchOptimizer->cancelBatchJob($job);

      wp_send_json_success(['message' => 'Batch job cancelled successfully']);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  private function sanitizeOptimizationSettings(array $settings): array
  {
    return [
      'jpg_quality' => (int) ($settings['jpg_quality'] ?? 80),
      'png_optimization' => sanitize_text_field($settings['png_optimization'] ?? 'o3'),
      'webp_quality' => (int) ($settings['webp_quality'] ?? 80),
      'avif_quality' => (int) ($settings['avif_quality'] ?? 80),
    ];
  }

  private function getJobById(string $jobId): \Squidge\ValueObjects\BatchJob
  {
    $status = $this->batchOptimizer->getBatchJobStatus($jobId);

    // Aqui seria criado um BatchJob a partir dos dados do status
    // Por simplicidade, vamos simular
    $config = new BatchConfiguration([1], ['jpg_quality' => 80, 'png_optimization' => 'o3', 'webp_quality' => 80, 'avif_quality' => 80]);
    $job = new \Squidge\ValueObjects\BatchJob($config);

    return $job;
  }
}
