<?php
// infrastructure/WordPress/Database/WordPressDatabaseAdapter.php

namespace Squidge\Infrastructure\WordPress\Database;

class WordPressDatabaseAdapter
{
  private \wpdb $wpdb;

  public function __construct(\wpdb $wpdb)
  {
    $this->wpdb = $wpdb;
  }

  public function getPrefix(): string
  {
    return $this->wpdb->prefix;
  }

  public function insert(string $table, array $data): int|false
  {
    $result = $this->wpdb->insert($table, $data);
    return $result ? $this->wpdb->insert_id : false;
  }

  public function getRow(string $query, array $args = []): array|object|null
  {
    if (empty($args)) {
      return $this->wpdb->get_row($query, 'ARRAY_A');
    }

    $prepared = $this->wpdb->prepare($query, $args);
    return $this->wpdb->get_row($prepared, 'ARRAY_A');
  }

  public function getResults(string $query, array $args = []): array
  {
    if (empty($args)) {
      return $this->wpdb->get_results($query, 'ARRAY_A');
    }

    $prepared = $this->wpdb->prepare($query, $args);
    return $this->wpdb->get_results($prepared, 'ARRAY_A');
  }

  public function getVar(string $query, array $args = []): string|null
  {
    if (empty($args)) {
      return $this->wpdb->get_var($query);
    }

    $prepared = $this->wpdb->prepare($query, $args);
    return $this->wpdb->get_var($prepared);
  }

  public function delete(string $table, array $where): int
  {
    return $this->wpdb->delete($table, $where);
  }

  public function query(string $query): int|false
  {
    return $this->wpdb->query($query);
  }
}
