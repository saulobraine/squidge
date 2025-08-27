<?php
// core/Database/Entities/OptimizationStatistics.php

namespace Squidge\Database\Entities;

class OptimizationStatistics
{
  private ?int $id = null;
  private int $attachmentId;
  private int $originalSize;
  private int $optimizedSize;
  private int $savedBytes;
  private float $savedPercentage;
  private string $toolUsed;
  private \DateTime $optimizationDate;
  private string $filePath;
  private string $mimeType;
  private string $status;
  private ?string $errorMessage = null;

  public function __construct(
    int $attachmentId,
    int $originalSize,
    int $optimizedSize,
    string $toolUsed,
    string $filePath,
    string $mimeType
  ) {
    $this->attachmentId = $attachmentId;
    $this->originalSize = $originalSize;
    $this->optimizedSize = $optimizedSize;
    $this->toolUsed = $toolUsed;
    $this->filePath = $filePath;
    $this->mimeType = $mimeType;
    $this->optimizationDate = new \DateTime();
    $this->status = 'pending';

    $this->calculateSavings();
  }

  private function calculateSavings(): void
  {
    $this->savedBytes = $this->originalSize - $this->optimizedSize;
    $this->savedPercentage = $this->originalSize > 0
      ? round(($this->savedBytes / $this->originalSize) * 100, 2)
      : 0.0;
  }

  // Getters
  public function getId(): ?int
  {
    return $this->id;
  }
  public function getAttachmentId(): int
  {
    return $this->attachmentId;
  }
  public function getOriginalSize(): int
  {
    return $this->originalSize;
  }
  public function getOptimizedSize(): int
  {
    return $this->optimizedSize;
  }
  public function getSavedBytes(): int
  {
    return $this->savedBytes;
  }
  public function getSavedPercentage(): float
  {
    return $this->savedPercentage;
  }
  public function getToolUsed(): string
  {
    return $this->toolUsed;
  }
  public function getOptimizationDate(): \DateTime
  {
    return $this->optimizationDate;
  }
  public function getFilePath(): string
  {
    return $this->filePath;
  }
  public function getMimeType(): string
  {
    return $this->mimeType;
  }
  public function getStatus(): string
  {
    return $this->status;
  }
  public function getErrorMessage(): ?string
  {
    return $this->errorMessage;
  }

  // Setters
  public function setId(int $id): void
  {
    $this->id = $id;
  }
  public function setStatus(string $status): void
  {
    $this->status = $status;
  }
  public function setErrorMessage(?string $errorMessage): void
  {
    $this->errorMessage = $errorMessage;
  }

  // Business Logic
  public function markAsSuccess(): void
  {
    $this->status = 'success';
    $this->errorMessage = null;
  }

  public function markAsError(string $errorMessage): void
  {
    $this->status = 'error';
    $this->errorMessage = $errorMessage;
  }

  public function isSuccessful(): bool
  {
    return $this->status === 'success';
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'attachment_id' => $this->attachmentId,
      'original_size' => $this->originalSize,
      'optimized_size' => $this->optimizedSize,
      'saved_bytes' => $this->savedBytes,
      'saved_percentage' => $this->savedPercentage,
      'tool_used' => $this->toolUsed,
      'optimization_date' => $this->optimizationDate->format('Y-m-d H:i:s'),
      'file_path' => $this->filePath,
      'mime_type' => $this->mimeType,
      'status' => $this->status,
      'error_message' => $this->errorMessage,
    ];
  }
}
