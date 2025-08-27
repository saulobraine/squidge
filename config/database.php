<?php
// config/database.php

return [
  'tables' => [
    'statistics' => [
      'name' => 'squidge_statistics',
      'charset' => 'utf8mb4',
      'collate' => 'utf8mb4_unicode_ci',
      'engine' => 'InnoDB',
    ],
  ],

  'migrations' => [
    'path' => SQUIDGE_MIGRATIONS_PATH,
    'namespace' => 'Squidge\Database\Migrations',
  ],

  'repositories' => [
    'statistics' => [
      'interface' => 'Squidge\Database\Contracts\StatisticsRepositoryInterface',
      'implementation' => 'Squidge\Database\Repositories\StatisticsRepository',
    ],
  ],

  'cleanup' => [
    'old_statistics_days' => 365, // 1 ano
    'batch_size' => 1000,
  ],
];
