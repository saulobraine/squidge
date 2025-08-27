<?php
// core/Container/ServiceContainer.php

namespace Squidge\Container;

class ServiceContainer
{
  private array $services = [];

  public function __construct()
  {
    $this->registerServices();
  }

  private function registerServices(): void
  {
    try {
      // Serviços básicos apenas para teste
      $this->services['test'] = 'test';
    } catch (\Exception $e) {
      error_log('Squidge ServiceContainer Error: ' . $e->getMessage());
    }
  }

  public function get(string $className): object
  {
    if (!isset($this->services[$className])) {
      throw new \InvalidArgumentException("Service not found: {$className}");
    }

    return $this->services[$className];
  }
}
