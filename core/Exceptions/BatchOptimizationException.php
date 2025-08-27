<?php
// core/Exceptions/BatchOptimizationException.php

namespace Squidge\Exceptions;

class BatchOptimizationException extends \Exception
{
  public static function invalidConfiguration(string $message, \Throwable $previous = null): self
  {
    return new self("Invalid batch configuration: {$message}", 0, $previous);
  }

  public static function jobNotFound(string $jobId, \Throwable $previous = null): self
  {
    return new self("Batch job not found: {$jobId}", 0, $previous);
  }

  public static function invalidJobStatus(string $jobId, string $currentStatus, string $expectedStatus, \Throwable $previous = null): self
  {
    return new self("Invalid job status for job {$jobId}: expected {$expectedStatus}, got {$currentStatus}", 0, $previous);
  }

  public static function optimizationFailed(string $message, \Throwable $previous = null): self
  {
    return new self("Optimization failed: {$message}", 0, $previous);
  }

  public static function progressTrackingFailed(string $message, \Throwable $previous = null): self
  {
    return new self("Progress tracking failed: {$message}", 0, $previous);
  }
}
