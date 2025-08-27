<?php
// core/Services/NotificationService.php

namespace Squidge\Services;

use Squidge\Services\Contracts\NotificationServiceInterface;

class NotificationService implements NotificationServiceInterface
{
  public function sendSuccessNotification(string $message, array $data = []): void
  {
    $this->logNotification('SUCCESS', $message, $data);
    $this->sendAdminNotification($message, 'success');
  }

  public function sendErrorNotification(string $message, array $data = []): void
  {
    $this->logNotification('ERROR', $message, $data);
    $this->sendAdminNotification($message, 'error');
  }

  public function sendProgressNotification(string $jobId, array $progress): void
  {
    $message = "Progress update for job {$jobId}";
    $this->logNotification('PROGRESS', $message, $progress);

    // Aqui seria implementado o envio de notificação em tempo real
    // Por exemplo, via WebSocket ou Server-Sent Events
  }

  public function sendBatchCompleteNotification(string $jobId, array $results): void
  {
    $totalImages = $results['total_images'] ?? 0;
    $savedBytes = $results['total_saved_bytes'] ?? 0;
    $formattedSize = $this->formatBytes($savedBytes);

    $message = "Batch job {$jobId} completed successfully. Processed {$totalImages} images, saved {$formattedSize}";

    $this->logNotification('BATCH_COMPLETE', $message, $results);
    $this->sendAdminNotification($message, 'success');

    // Enviar email de notificação se configurado
    $this->sendEmailNotification($message, $results);
  }

  private function logNotification(string $type, string $message, array $data): void
  {
    $logData = [
      'timestamp' => date('Y-m-d H:i:s'),
      'type' => $type,
      'message' => $message,
      'data' => $data
    ];

    error_log('Squidge Notification: ' . json_encode($logData));
  }

  private function sendAdminNotification(string $message, string $type): void
  {
    // Aqui seria implementado o sistema de notificações admin do WordPress
    // Por exemplo, usando transients para notificações temporárias
    $notification = [
      'message' => $message,
      'type' => $type,
      'timestamp' => time()
    ];

    set_transient('squidge_admin_notification', $notification, 60 * 5); // 5 minutos
  }

  private function sendEmailNotification(string $message, array $data): void
  {
    $adminEmail = get_option('admin_email');
    if (!$adminEmail) {
      return;
    }

    $subject = 'Squidge Batch Optimization Complete';
    $body = $this->formatEmailBody($message, $data);

    wp_mail($adminEmail, $subject, $body, [
      'Content-Type: text/html; charset=UTF-8'
    ]);
  }

  private function formatEmailBody(string $message, array $data): string
  {
    $body = "<h2>Squidge Batch Optimization</h2>";
    $body .= "<p>{$message}</p>";

    if (!empty($data)) {
      $body .= "<h3>Details:</h3>";
      $body .= "<ul>";

      foreach ($data as $key => $value) {
        if (is_array($value)) {
          $value = json_encode($value);
        }
        $body .= "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> {$value}</li>";
      }

      $body .= "</ul>";
    }

    $body .= "<hr>";
    $body .= "<p><small>This is an automated notification from Squidge plugin.</small></p>";

    return $body;
  }

  private function formatBytes(int $bytes): string
  {
    if ($bytes < 1024) {
      return $bytes . ' B';
    }

    if ($bytes < 1024 * 1024) {
      return round($bytes / 1024, 2) . ' KB';
    }

    if ($bytes < 1024 * 1024 * 1024) {
      return round($bytes / (1024 * 1024), 2) . ' MB';
    }

    return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  }
}
