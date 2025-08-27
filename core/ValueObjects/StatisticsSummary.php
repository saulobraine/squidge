<?php
// core/ValueObjects/StatisticsSummary.php

namespace Squidge\ValueObjects;

class StatisticsSummary
{
  private int $totalImages;
  private int $totalOriginalSize;
  private int $totalOptimizedSize;
  private int $totalSavedBytes;
  private float $averageReduction;
  private int $successfulOptimizations;
  private int $failedOptimizations;
  private float $successRate;
  private \DateTime $lastOptimization;
  private array $toolBreakdown;

  public function __construct(array $data)
  {
    $this->totalImages = $data['total_images'] ?? 0;
    $this->totalOriginalSize = $data['total_original_size'] ?? 0;
    $this->totalOptimizedSize = $data['total_optimized_size'] ?? 0;
    $this->totalSavedBytes = $data['total_saved_bytes'] ?? 0;
    $this->averageReduction = $data['average_reduction'] ?? 0.0;
    $this->successfulOptimizations = $data['successful_optimizations'] ?? 0;
    $this->failedOptimizations = $data['failed_optimizations'] ?? 0;
    $this->lastOptimization = new \DateTime($data['last_optimization'] ?? 'now');
    $this->toolBreakdown = $data['tool_breakdown'] ?? [];

    $this->calculateSuccessRate();
  }

  private function calculateSuccessRate(): void
  {
    $total = $this->successfulOptimizations + $this->failedOptimizations;
    $this->successRate = $total > 0 ? round(($this->successfulOptimizations / $total) * 100, 2) : 0.0;
  }

  public function getTotalSavedMB(): float
  {
    return round($this->totalSavedBytes / 1024 / 1024, 2);
  }

  public function getTotalSavedGB(): float
  {
    return round($this->totalSavedBytes / 1024 / 1024 / 1024, 2);
  }

  public function getOptimizationEfficiency(): string
  {
    if ($this->averageReduction >= 30) return 'Excelente';
    if ($this->averageReduction >= 20) return 'Muito Bom';
    if ($this->averageReduction >= 15) return 'Bom';
    if ($this->averageReduction >= 10) return 'Regular';
    return 'Baixo';
  }

  public function getPerformanceGrade(): string
  {
    if ($this->successRate >= 95) return 'A+';
    if ($this->successRate >= 90) return 'A';
    if ($this->successRate >= 80) return 'B';
    if ($this->successRate >= 70) return 'C';
    if ($this->successRate >= 60) return 'D';
    return 'F';
  }

  public function getFormattedLastOptimization(): string
  {
    $now = new \DateTime();
    $diff = $now->diff($this->lastOptimization);

    if ($diff->days > 0) {
      return "{$diff->days} dia(s) atrás";
    }
    if ($diff->h > 0) {
      return "{$diff->h} hora(s) atrás";
    }
    if ($diff->i > 0) {
      return "{$diff->i} minuto(s) atrás";
    }
    return "Agora mesmo";
  }

  // Getters
  public function getTotalImages(): int
  {
    return $this->totalImages;
  }
  public function getTotalOriginalSize(): int
  {
    return $this->totalOriginalSize;
  }
  public function getTotalOptimizedSize(): int
  {
    return $this->totalOptimizedSize;
  }
  public function getTotalSavedBytes(): int
  {
    return $this->totalSavedBytes;
  }
  public function getAverageReduction(): float
  {
    return $this->averageReduction;
  }
  public function getSuccessfulOptimizations(): int
  {
    return $this->successfulOptimizations;
  }
  public function getFailedOptimizations(): int
  {
    return $this->failedOptimizations;
  }
  public function getSuccessRate(): float
  {
    return $this->successRate;
  }
  public function getLastOptimization(): \DateTime
  {
    return $this->lastOptimization;
  }
  public function getToolBreakdown(): array
  {
    return $this->toolBreakdown;
  }

  public function toArray(): array
  {
    return [
      'total_images' => $this->totalImages,
      'total_original_size' => $this->totalOriginalSize,
      'total_optimized_size' => $this->totalOptimizedSize,
      'total_saved_bytes' => $this->totalSavedBytes,
      'total_saved_mb' => $this->getTotalSavedMB(),
      'total_saved_gb' => $this->getTotalSavedGB(),
      'average_reduction' => $this->averageReduction,
      'successful_optimizations' => $this->successfulOptimizations,
      'failed_optimizations' => $this->failedOptimizations,
      'success_rate' => $this->successRate,
      'last_optimization' => $this->lastOptimization->format('Y-m-d H:i:s'),
      'formatted_last_optimization' => $this->getFormattedLastOptimization(),
      'tool_breakdown' => $this->toolBreakdown,
      'optimization_efficiency' => $this->getOptimizationEfficiency(),
      'performance_grade' => $this->getPerformanceGrade(),
    ];
  }
}
