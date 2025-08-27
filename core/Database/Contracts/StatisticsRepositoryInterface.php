<?php
// core/Database/Contracts/StatisticsRepositoryInterface.php

namespace Squidge\Database\Contracts;

use Squidge\Database\Entities\OptimizationStatistics;

interface StatisticsRepositoryInterface
{
	public function save(OptimizationStatistics $statistics): bool;
	public function findById(int $id): ?OptimizationStatistics;
	public function findByAttachmentId(int $attachmentId): array;
	public function getAggregatedStats(): array;
	public function delete(int $id): bool;
	public function exists(int $id): bool;
}
