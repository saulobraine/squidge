<?php
// core/Services/OptimizationTracker.php

namespace Squidge\Services;

class OptimizationTracker
{
  public function simulateOptimization(string $filePath, int $originalSize): int
  {
    $reductionFactor = rand(20, 40) / 100;
    return (int) ($originalSize * (1 - $reductionFactor));
  }

  public function getFileSize(string $filePath): int
  {
    if (!file_exists($filePath)) {
      throw new \InvalidArgumentException("File not found: {$filePath}");
    }

    $size = filesize($filePath);
    if ($size === false) {
      throw new \RuntimeException("Unable to get file size: {$filePath}");
    }

    return $size;
  }
}
