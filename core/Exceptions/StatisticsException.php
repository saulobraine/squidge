<?php
// core/Exceptions/StatisticsException.php

namespace Squidge\Exceptions;

class StatisticsException extends \Exception
{
  public static function databaseError(string $message, \Throwable $previous = null): self
  {
    return new self("Database error: {$message}", 0, $previous);
  }

  public static function invalidData(string $message, \Throwable $previous = null): self
  {
    return new self("Invalid data: {$message}", 0, $previous);
  }

  public static function fileNotFound(string $filePath, \Throwable $previous = null): self
  {
    return new self("File not found: {$filePath}", 0, $previous);
  }

  public static function optimizationFailed(string $message, \Throwable $previous = null): self
  {
    return new self("Optimization failed: {$message}", 0, $previous);
  }
}
