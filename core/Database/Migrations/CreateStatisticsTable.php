<?php
// core/Database/Migrations/CreateStatisticsTable.php

namespace Squidge\Database\Migrations;

use Squidge\Infrastructure\WordPress\Database\WordPressDatabaseAdapter;

class CreateStatisticsTable
{
  private WordPressDatabaseAdapter $database;

  public function __construct(WordPressDatabaseAdapter $database)
  {
    $this->database = $database;
  }

  public function up(): bool
  {
    $tableName = $this->database->getPrefix() . 'squidge_statistics';

    $sql = "
            CREATE TABLE IF NOT EXISTS `{$tableName}` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `attachment_id` bigint(20) unsigned NOT NULL,
                `original_size` bigint(20) unsigned NOT NULL,
                `optimized_size` bigint(20) unsigned NOT NULL,
                `saved_bytes` bigint(20) unsigned NOT NULL,
                `saved_percentage` decimal(5,2) NOT NULL,
                `tool_used` varchar(50) NOT NULL,
                `optimization_date` datetime NOT NULL,
                `file_path` varchar(500) NOT NULL,
                `mime_type` varchar(100) NOT NULL,
                `status` enum('success','error','pending') DEFAULT 'pending',
                `error_message` text,
                PRIMARY KEY (`id`),
                KEY `attachment_id` (`attachment_id`),
                KEY `tool_used` (`tool_used`),
                KEY `optimization_date` (`optimization_date`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

    return $this->database->query($sql) !== false;
  }

  public function down(): bool
  {
    $tableName = $this->database->getPrefix() . 'squidge_statistics';
    $sql = "DROP TABLE IF EXISTS `{$tableName}`";

    return $this->database->query($sql) !== false;
  }
}
