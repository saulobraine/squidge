<?php
// core/Services/BatchOptimizationService.php

namespace Squidge\Services;

use Squidge\Services\Contracts\BatchOptimizationInterface;
use Squidge\Services\Contracts\ProgressTrackerInterface;
use Squidge\Services\Contracts\NotificationServiceInterface;
use Squidge\ValueObjects\BatchJob;
use Squidge\ValueObjects\BatchConfiguration;
use Squidge\Exceptions\BatchOptimizationException;

class BatchOptimizationService implements BatchOptimizationInterface
{
  private ProgressTrackerInterface $progressTracker;
  private NotificationServiceInterface $notificationService;
  private array $activeJobs = [];

  public function __construct(
    ProgressTrackerInterface $progressTracker,
    NotificationServiceInterface $notificationService
  ) {
    $this->progressTracker = $progressTracker;
    $this->notificationService = $notificationService;
  }

  public function startBatchJob(BatchConfiguration $config): BatchJob
  {
    try {
      $job = new BatchJob($config);
      $this->activeJobs[$job->getId()] = $job;

      $this->progressTracker->trackProgress($job->getId(), $this->createProgress($job));
      $this->notificationService->sendSuccessNotification('Batch job started successfully', ['job_id' => $job->getId()]);

      return $job;
    } catch (\Exception $e) {
      throw BatchOptimizationException::invalidConfiguration($e->getMessage(), $e);
    }
  }

  public function processBatch(BatchJob $job): void
  {
    if (!$this->jobExists($job->getId())) {
      throw BatchOptimizationException::jobNotFound($job->getId());
    }

    try {
      $job->start();
      $this->processImages($job);
      $job->complete();

      $this->notificationService->sendBatchCompleteNotification($job->getId(), $job->toArray());
    } catch (\Exception $e) {
      $job->markAsError($e->getMessage());
      $this->notificationService->sendErrorNotification('Batch processing failed: ' . $e->getMessage());
      throw BatchOptimizationException::optimizationFailed($e->getMessage(), $e);
    }
  }

  public function pauseBatchJob(BatchJob $job): void
  {
    if (!$this->jobExists($job->getId())) {
      throw BatchOptimizationException::jobNotFound($job->getId());
    }

    $job->pause();
    $this->notificationService->sendProgressNotification($job->getId(), ['status' => 'paused']);
  }

  public function resumeBatchJob(BatchJob $job): void
  {
    if (!$this->jobExists($job->getId())) {
      throw BatchOptimizationException::jobNotFound($job->getId());
    }

    $job->resume();
    $this->notificationService->sendProgressNotification($job->getId(), ['status' => 'resumed']);
  }

  public function cancelBatchJob(BatchJob $job): void
  {
    if (!$this->jobExists($job->getId())) {
      throw BatchOptimizationException::jobNotFound($job->getId());
    }

    $job->cancel();
    $this->notificationService->sendProgressNotification($job->getId(), ['status' => 'cancelled']);
  }

  public function getBatchJobStatus(string $jobId): array
  {
    if (!$this->jobExists($jobId)) {
      throw BatchOptimizationException::jobNotFound($jobId);
    }

    $job = $this->activeJobs[$jobId];
    $progress = $this->progressTracker->getProgress($jobId);

    return array_merge($job->toArray(), [
      'progress' => $progress ? $progress->toArray() : null
    ]);
  }

  public function getAvailableImages(): array
  {
    global $wpdb;

    $query = "
            SELECT p.ID, p.post_title, pm.meta_value as file_path
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND pm.meta_key = '_wp_attached_file'
            ORDER BY p.post_date DESC
        ";

    $results = $wpdb->get_results($query, ARRAY_A);

    return array_map(function ($row) {
      return [
        'id' => (int) $row['ID'],
        'title' => $row['post_title'],
        'file_path' => $row['file_path'],
        'file_name' => basename($row['file_path']),
        'file_size' => $this->getFileSize($row['file_path']),
        'mime_type' => get_post_mime_type($row['ID'])
      ];
    }, $results);
  }

  public function getBatchJobHistory(): array
  {
    return array_map(function ($job) {
      return $job->toArray();
    }, $this->activeJobs);
  }

  private function jobExists(string $jobId): bool
  {
    return isset($this->activeJobs[$jobId]);
  }

  private function createProgress(BatchJob $job): \Squidge\ValueObjects\OptimizationProgress
  {
    return new \Squidge\ValueObjects\OptimizationProgress($job->getId(), $job->getTotalImages());
  }

  private function processImages(BatchJob $job): void
  {
    $config = $job->getConfiguration();
    $imageIds = $config->getImageIds();
    $batchSize = $config->getBatchSize();

    $totalImages = count($imageIds);
    $processed = 0;
    $successful = 0;
    $failed = 0;
    $savedBytes = 0;

    for ($i = 0; $i < $totalImages; $i += $batchSize) {
      $batch = array_slice($imageIds, $i, $batchSize);

      foreach ($batch as $imageId) {
        try {
          $result = $this->optimizeImage($imageId, $config);
          $savedBytes += $result['saved_bytes'];
          $successful++;
        } catch (\Exception $e) {
          $failed++;
          error_log("Failed to optimize image {$imageId}: " . $e->getMessage());
        }

        $processed++;
        $this->updateJobProgress($job, $processed, $successful, $failed, $savedBytes);

        if ($job->getStatus() === BatchJob::STATUS_PAUSED) {
          return;
        }
      }
    }
  }

  private function optimizeImage(int $imageId, BatchConfiguration $config): array
  {
    $filePath = get_attached_file($imageId);
    if (!$filePath || !file_exists($filePath)) {
      throw new \Exception("File not found for image {$imageId}");
    }

    $originalSize = filesize($filePath);
    $settings = $config->getOptimizationSettings();

    // Simular otimização (aqui seria integrado com os serviços reais do Squidge)
    $reductionFactor = rand(15, 35) / 100;
    $optimizedSize = (int) ($originalSize * (1 - $reductionFactor));
    $savedBytes = $originalSize - $optimizedSize;

    return [
      'image_id' => $imageId,
      'original_size' => $originalSize,
      'optimized_size' => $optimizedSize,
      'saved_bytes' => $savedBytes,
      'reduction_percentage' => round($reductionFactor * 100, 2)
    ];
  }

  private function updateJobProgress(BatchJob $job, int $processed, int $successful, int $failed, int $savedBytes): void
  {
    $job->updateProgress($processed, $successful, $failed, $savedBytes);

    $progress = $this->progressTracker->getProgress($job->getId());
    if ($progress) {
      $progress->updateProgress($processed, $successful, $failed, $savedBytes);
    }

    $this->notificationService->sendProgressNotification($job->getId(), [
      'processed' => $processed,
      'successful' => $successful,
      'failed' => $failed,
      'saved_bytes' => $savedBytes
    ]);
  }

  private function getFileSize(string $filePath): int
  {
    $fullPath = WP_CONTENT_DIR . '/uploads/' . $filePath;
    return file_exists($fullPath) ? filesize($fullPath) : 0;
  }
}
