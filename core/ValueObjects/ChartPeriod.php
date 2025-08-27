<?php
// core/ValueObjects/ChartPeriod.php

namespace Squidge\ValueObjects;

class ChartPeriod
{
  private string $period;
  private \DateTime $startDate;
  private \DateTime $endDate;
  private int $days;

  public const PERIOD_DAY = 'day';
  public const PERIOD_WEEK = 'week';
  public const PERIOD_MONTH = 'month';
  public const PERIOD_QUARTER = 'quarter';
  public const PERIOD_YEAR = 'year';

  public function __construct(string $period = self::PERIOD_MONTH)
  {
    $this->validatePeriod($period);
    $this->period = $period;
    $this->calculateDateRange();
  }

  private function validatePeriod(string $period): void
  {
    $validPeriods = [
      self::PERIOD_DAY,
      self::PERIOD_WEEK,
      self::PERIOD_MONTH,
      self::PERIOD_QUARTER,
      self::PERIOD_YEAR
    ];

    if (!in_array($period, $validPeriods)) {
      throw new \InvalidArgumentException("Invalid period: {$period}");
    }
  }

  private function calculateDateRange(): void
  {
    $this->endDate = new \DateTime();

    switch ($this->period) {
      case self::PERIOD_DAY:
        $this->startDate = (new \DateTime())->modify('-1 day');
        $this->days = 1;
        break;
      case self::PERIOD_WEEK:
        $this->startDate = (new \DateTime())->modify('-1 week');
        $this->days = 7;
        break;
      case self::PERIOD_MONTH:
        $this->startDate = (new \DateTime())->modify('-1 month');
        $this->days = 30;
        break;
      case self::PERIOD_QUARTER:
        $this->startDate = (new \DateTime())->modify('-3 months');
        $this->days = 90;
        break;
      case self::PERIOD_YEAR:
        $this->startDate = (new \DateTime())->modify('-1 year');
        $this->days = 365;
        break;
    }
  }

  public function getDateRange(): array
  {
    return [
      'start' => $this->startDate->format('Y-m-d'),
      'end' => $this->endDate->format('Y-m-d'),
      'days' => $this->days
    ];
  }

  public function getFormattedPeriod(): string
  {
    $formats = [
      self::PERIOD_DAY => 'Último dia',
      self::PERIOD_WEEK => 'Última semana',
      self::PERIOD_MONTH => 'Último mês',
      self::PERIOD_QUARTER => 'Último trimestre',
      self::PERIOD_YEAR => 'Último ano'
    ];

    return $formats[$this->period];
  }

  public function getSQLDateFilter(): string
  {
    return "optimization_date >= '{$this->startDate->format('Y-m-d H:i:s')}'";
  }

  // Getters
  public function getPeriod(): string
  {
    return $this->period;
  }
  public function getStartDate(): \DateTime
  {
    return $this->startDate;
  }
  public function getEndDate(): \DateTime
  {
    return $this->endDate;
  }
  public function getDays(): int
  {
    return $this->days;
  }
}
