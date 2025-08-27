<?php
// core/ValueObjects/OptimizationResult.php

namespace Squidge\ValueObjects;

class OptimizationResult
{
  private int $attachmentId;
  private string $filePath;
  private int $originalSize;
  private int $optimizedSize;
  private string $toolUsed;
  private string $status;
  private \DateTime $timestamp;
  private ?string $errorMessage;
  private float $compressionRatio;
  private int $savedBytes;

  public function __construct(
    int $attachmentId,
    string $filePath,
    int $originalSize,
    int $optimizedSize,
    string $toolUsed,
    string $status = 'pending'
  ) {
    $this->attachmentId = $attachmentId;
    $this->filePath = $filePath;
    $this->originalSize = $originalSize;
    $this->optimizedSize = $optimizedSize;
    $this->toolUsed = $toolUsed;
    $this->status = $status;
    $this->timestamp = new \DateTime();

    $this->calculateMetrics();
  }

  private function calculateMetrics(): void
  {
    $this->savedBytes = $this->originalSize - $this->optimizedSize;
    $this->compressionRatio = $this->originalSize > 0
      ? round(($this->savedBytes / $this->originalSize) * 100, 2)
      : 0.0;
  }

  public function markAsSuccess(): void
  {
    $this->status = 'success';
    $this->errorMessage = null;
  }

  public function markAsFailed(string $errorMessage): void
  {
    $this->status = 'failed';
    $this->errorMessage = $errorMessage;
  }

  public function isSuccessful(): bool
  {
    return $this->status === 'success';
  }

  public function getEfficiencyScore(): int
  {
    if ($this->compressionRatio >= 30) return 5;
    if ($this->compressionRatio >= 20) return 4;
    if ($this->compressionRatio >= 15) return 3;
    if ($this->compressionRatio >= 10) return 2;
    return 1;
  }

  // Getters
  public function getAttachmentId(): int
  {
    return $this->attachmentId;
  }
  public function getFilePath(): string
  {
    return $this->filePath;
  }
  public function getOriginalSize(): int
  {
    return $this->originalSize;
  }
  public function getOptimizedSize(): int
  {
    return $this->optimizedSize;
  }
  public function getToolUsed(): string
  {
    return $this->toolUsed;
  }
  public function getStatus(): string
  {
    return $this->status;
  }
  public function getTimestamp(): \DateTime
  {
    return $this->timestamp;
  }
  public function getErrorMessage(): ?string
  {
    return $this->errorMessage;
  }
  public function getCompressionRatio(): float
  {
    return $this->compressionRatio;
  }
  public function getSavedBytes(): int
  {
    return $this->savedBytes;
  }

  public function toArray(): array
  {
    return [
      'attachment_id' => $this->attachmentId,
      'file_path' => $this->filePath,
      'original_size' => $this->originalSize,
      'optimized_size' => $this->optimizedSize,
      'tool_used' => $this->toolUsed,
      'status' => $this->status,
      'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
      'error_message' => $this->errorMessage,
      'compression_ratio' => $this->compressionRatio,
      'saved_bytes' => $this->savedBytes,
      'efficiency_score' => $this->getEfficiencyScore(),
    ];
  }
}
