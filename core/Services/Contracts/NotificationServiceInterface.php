<?php
// core/Services/Contracts/NotificationServiceInterface.php

namespace Squidge\Services\Contracts;

interface NotificationServiceInterface
{
  public function sendSuccessNotification(string $message, array $data = []): void;
  public function sendErrorNotification(string $message, array $data = []): void;
  public function sendProgressNotification(string $jobId, array $progress): void;
  public function sendBatchCompleteNotification(string $jobId, array $results): void;
}
