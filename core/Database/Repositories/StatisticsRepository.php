<?php
// core/Database/Repositories/StatisticsRepository.php

namespace Squidge\Database\Repositories;

use Squidge\Database\Contracts\StatisticsRepositoryInterface;
use Squidge\Database\Entities\OptimizationStatistics;
use Squidge\Infrastructure\WordPress\Database\WordPressDatabaseAdapter;

class StatisticsRepository implements StatisticsRepositoryInterface
{
  private WordPressDatabaseAdapter $database;
  private string $tableName;

  public function __construct(WordPressDatabaseAdapter $database)
  {
    $this->database = $database;
    $this->tableName = $this->database->getPrefix() . 'squidge_statistics';
  }

  public function save(OptimizationStatistics $statistics): bool
  {
    $data = $statistics->toArray();
    unset($data['id']); // Remove ID for insert

    $result = $this->database->insert($this->tableName, $data);

    if ($result && $result > 0) {
      $statistics->setId($result);
      return true;
    }

    return false;
  }

  public function findById(int $id): ?OptimizationStatistics
  {
    $row = $this->database->getRow(
      "SELECT * FROM {$this->tableName} WHERE id = %d",
      [$id]
    );

    return $row ? $this->hydrateFromRow($row) : null;
  }

  public function findByAttachmentId(int $attachmentId): array
  {
    $rows = $this->database->getResults(
      "SELECT * FROM {$this->tableName} WHERE attachment_id = %d ORDER BY optimization_date DESC",
      [$attachmentId]
    );

    return array_map([$this, 'hydrateFromRow'], $rows);
  }

  public function getAggregatedStats(): array
  {
    $sql = "
            SELECT
                COUNT(DISTINCT attachment_id) as total_images,
                SUM(original_size) as total_original_size,
                SUM(optimized_size) as total_optimized_size,
                SUM(saved_bytes) as total_saved_bytes,
                AVG(saved_percentage) as average_reduction
            FROM {$this->tableName}
            WHERE status = 'success'
        ";

    return $this->database->getRow($sql) ?: [];
  }

  public function delete(int $id): bool
  {
    return $this->database->delete($this->tableName, ['id' => $id]) > 0;
  }

  public function exists(int $id): bool
  {
    $count = $this->database->getVar(
      "SELECT COUNT(*) FROM {$this->tableName} WHERE id = %d",
      [$id]
    );

    return $count > 0;
  }

  private function hydrateFromRow(array $row): OptimizationStatistics
  {
    $statistics = new OptimizationStatistics(
      (int) $row['attachment_id'],
      (int) $row['original_size'],
      (int) $row['optimized_size'],
      $row['tool_used'],
      $row['file_path'],
      $row['mime_type']
    );

    $statistics->setId((int) $row['id']);
    $statistics->setStatus($row['status']);
    $statistics->setErrorMessage($row['error_message']);

    return $statistics;
  }
}
