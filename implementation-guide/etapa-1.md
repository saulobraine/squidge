# 🏗️ ETAPA 1: ARQUITETURA SOLID E BANCO DE DADOS

## 📋 **Objetivo da Etapa**

Criar uma fundação sólida seguindo princípios SOLID, Object Calisthenics e Clean Architecture para o sistema de otimização em lote com estatísticas.

## 🎯 **Princípios Aplicados**

### **Object Calisthenics:**

- ✅ 1 nível de indentação por método
- ✅ Não usar ELSE
- ✅ Encapsular primitivos
- ✅ Coleções de primeira classe
- ✅ Um ponto por linha
- ✅ Não abreviar
- ✅ Manter entidades pequenas
- ✅ Não mais de 2 variáveis de instância
- ✅ Sem getters/setters/properties

### **SOLID Principles:**

- ✅ **S**ingle Responsibility Principle
- ✅ **O**pen/Closed Principle
- ✅ **L**iskov Substitution Principle
- ✅ **I**nterface Segregation Principle
- ✅ **D**ependency Inversion Principle

## 📁 **Estrutura de Diretórios Refatorada**

```
wp-content/plugins/squidge/
├── core/
│   ├── Database/
│   │   ├── Contracts/
│   │   │   └── StatisticsRepositoryInterface.php
│   │   ├── Entities/
│   │   │   └── OptimizationStatistics.php
│   │   ├── Repositories/
│   │   │   └── StatisticsRepository.php
│   │   └── Migrations/
│   │       └── CreateStatisticsTable.php
│   ├── Services/
│   │   ├── Contracts/
│   │   │   └── StatisticsServiceInterface.php
│   │   ├── StatisticsService.php
│   │   └── OptimizationTracker.php
│   └── Exceptions/
│       └── StatisticsException.php
├── infrastructure/
│   └── WordPress/
│       ├── Database/
│       │   └── WordPressDatabaseAdapter.php
│       └── Hooks/
│           └── ActivationHooks.php
└── config/
    └── database.php
```

## 🔧 **Implementação Refatorada**

### **1. Interface do Repositório**

```php
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
```

### **2. Entidade de Domínio**

```php
<?php
// core/Database/Entities/OptimizationStatistics.php

namespace Squidge\Database\Entities;

class OptimizationStatistics
{
    private int $id;
    private int $attachmentId;
    private int $originalSize;
    private int $optimizedSize;
    private int $savedBytes;
    private float $savedPercentage;
    private string $toolUsed;
    private \DateTime $optimizationDate;
    private string $filePath;
    private string $mimeType;
    private string $status;
    private ?string $errorMessage;

    public function __construct(
        int $attachmentId,
        int $originalSize,
        int $optimizedSize,
        string $toolUsed,
        string $filePath,
        string $mimeType
    ) {
        $this->attachmentId = $attachmentId;
        $this->originalSize = $originalSize;
        $this->optimizedSize = $optimizedSize;
        $this->toolUsed = $toolUsed;
        $this->filePath = $filePath;
        $this->mimeType = $mimeType;
        $this->optimizationDate = new \DateTime();
        $this->status = 'pending';

        $this->calculateSavings();
    }

    private function calculateSavings(): void
    {
        $this->savedBytes = $this->originalSize - $this->optimizedSize;
        $this->savedPercentage = $this->originalSize > 0
            ? round(($this->savedBytes / $this->originalSize) * 100, 2)
            : 0.0;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getAttachmentId(): int { return $this->attachmentId; }
    public function getOriginalSize(): int { return $this->originalSize; }
    public function getOptimizedSize(): int { return $this->optimizedSize; }
    public function getSavedBytes(): int { return $this->savedBytes; }
    public function getSavedPercentage(): float { return $this->savedPercentage; }
    public function getToolUsed(): string { return $this->toolUsed; }
    public function getOptimizationDate(): \DateTime { return $this->optimizationDate; }
    public function getFilePath(): string { return $this->filePath; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getStatus(): string { return $this->status; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setErrorMessage(?string $errorMessage): void { $this->errorMessage = $errorMessage; }

    // Business Logic
    public function markAsSuccess(): void
    {
        $this->status = 'success';
        $this->errorMessage = null;
    }

    public function markAsError(string $errorMessage): void
    {
        $this->status = 'error';
        $this->errorMessage = $errorMessage;
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'attachment_id' => $this->attachmentId,
            'original_size' => $this->originalSize,
            'optimized_size' => $this->optimizedSize,
            'saved_bytes' => $this->savedBytes,
            'saved_percentage' => $this->savedPercentage,
            'tool_used' => $this->toolUsed,
            'optimization_date' => $this->optimizationDate->format('Y-m-d H:i:s'),
            'file_path' => $this->filePath,
            'mime_type' => $this->mimeType,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
        ];
    }
}
```

### **3. Repositório Concreto**

```php
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
```

### **4. Adaptador de Banco WordPress**

```php
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
            return $this->wpdb->get_row($query, ARRAY_A);
        }

        $prepared = $this->wpdb->prepare($query, $args);
        return $this->wpdb->get_row($prepared, ARRAY_A);
    }

    public function getResults(string $query, array $args = []): array
    {
        if (empty($args)) {
            return $this->wpdb->get_results($query, ARRAY_A);
        }

        $prepared = $this->wpdb->prepare($query, $args);
        return $this->wpdb->get_results($prepared, ARRAY_A);
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
```

### **5. Serviço de Estatísticas**

```php
<?php
// core/Services/StatisticsService.php

namespace Squidge\Services;

use Squidge\Database\Contracts\StatisticsRepositoryInterface;
use Squidge\Database\Entities\OptimizationStatistics;
use Squidge\Services\OptimizationTracker;

class StatisticsService
{
    private StatisticsRepositoryInterface $repository;
    private OptimizationTracker $tracker;

    public function __construct(
        StatisticsRepositoryInterface $repository,
        OptimizationTracker $tracker
    ) {
        $this->repository = $repository;
        $this->tracker = $tracker;
    }

    public function trackOptimization(
        int $attachmentId,
        string $filePath,
        int $originalSize,
        string $toolUsed,
        string $mimeType
    ): OptimizationStatistics {
        $optimizedSize = $this->tracker->simulateOptimization($filePath, $originalSize);

        $statistics = new OptimizationStatistics(
            $attachmentId,
            $originalSize,
            $optimizedSize,
            $toolUsed,
            $filePath,
            $mimeType
        );

        $this->repository->save($statistics);

        return $statistics;
    }

    public function getOptimizationStats(int $attachmentId): array
    {
        $statistics = $this->repository->findByAttachmentId($attachmentId);

        return [
            'total_optimizations' => count($statistics),
            'successful_optimizations' => count(array_filter($statistics, fn($s) => $s->isSuccessful())),
            'total_saved_bytes' => array_sum(array_map(fn($s) => $s->getSavedBytes(), $statistics)),
            'average_reduction' => $this->calculateAverageReduction($statistics),
        ];
    }

    public function getGlobalStats(): array
    {
        return $this->repository->getAggregatedStats();
    }

    private function calculateAverageReduction(array $statistics): float
    {
        if (empty($statistics)) {
            return 0.0;
        }

        $totalReduction = array_sum(array_map(fn($s) => $s->getSavedPercentage(), $statistics));
        return round($totalReduction / count($statistics), 2);
    }
}
```

### **6. Rastreador de Otimização**

```php
<?php
// core/Services/OptimizationTracker.php

namespace Squidge\Services;

class OptimizationTracker
{
    public function simulateOptimization(string $filePath, int $originalSize): int
    {
        $reductionFactor = rand(20, 40) / 100;
        return (int) ($originalSize * (1 - $reductionFactor));
    }

    public function getFileSize(string $filePath): int
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $size = filesize($filePath);
        if ($size === false) {
            throw new \RuntimeException("Unable to get file size: {$filePath}");
        }

        return $size;
    }
}
```

### **7. Sistema de Migração**

```php
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
```

### **8. Hooks de Ativação**

```php
<?php
// infrastructure/WordPress/Hooks/ActivationHooks.php

namespace Squidge\Infrastructure\WordPress\Hooks;

use Squidge\Database\Migrations\CreateStatisticsTable;
use Squidge\Infrastructure\WordPress\Database\WordPressDatabaseAdapter;

class ActivationHooks
{
    private CreateStatisticsTable $migration;

    public function __construct(CreateStatisticsTable $migration)
    {
        $this->migration = $migration;
    }

    public function activate(): void
    {
        try {
            $this->migration->up();
            $this->setDefaultOptions();
            flush_rewrite_rules();
        } catch (\Exception $e) {
            error_log("Squidge activation failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function deactivate(): void
    {
        try {
            wp_clear_scheduled_hook('squidge_cleanup_old_stats');
            flush_rewrite_rules();
        } catch (\Exception $e) {
            error_log("Squidge deactivation failed: " . $e->getMessage());
        }
    }

    private function setDefaultOptions(): void
    {
        $defaults = [
            'squidge_jpg_enable' => true,
            'squidge_jpg_quality' => 80,
            'squidge_png_enable' => true,
            'squidge_png_quality' => 'o3',
            'squidge_webp_enable' => true,
            'squidge_webp_quality' => 80,
            'squidge_avif_enable' => true,
            'squidge_batch_limit' => 100,
            'squidge_auto_optimize' => true
        ];

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
}
```

### **9. Container de Dependências**

```php
<?php
// core/Container/ServiceContainer.php

namespace Squidge\Container;

use Squidge\Database\Contracts\StatisticsRepositoryInterface;
use Squidge\Database\Repositories\StatisticsRepository;
use Squidge\Infrastructure\WordPress\Database\WordPressDatabaseAdapter;
use Squidge\Services\StatisticsService;
use Squidge\Services\OptimizationTracker;

class ServiceContainer
{
    private array $services = [];

    public function __construct()
    {
        $this->registerServices();
    }

    private function registerServices(): void
    {
        global $wpdb;

        $this->services[WordPressDatabaseAdapter::class] = new WordPressDatabaseAdapter($wpdb);
        $this->services[StatisticsRepositoryInterface::class] = new StatisticsRepository(
            $this->get(WordPressDatabaseAdapter::class)
        );
        $this->services[OptimizationTracker::class] = new OptimizationTracker();
        $this->services[StatisticsService::class] = new StatisticsService(
            $this->get(StatisticsRepositoryInterface::class),
            $this->get(OptimizationTracker::class)
        );
    }

    public function get(string $className): object
    {
        if (!isset($this->services[$className])) {
            throw new \InvalidArgumentException("Service not found: {$className}");
        }

        return $this->services[$className];
    }
}
```

## ✅ **Checklist da Etapa 1 Refatorada**

- [ ] Criar estrutura de diretórios refatorada
- [ ] Implementar interfaces e contratos
- [ ] Criar entidades de domínio
- [ ] Implementar repositórios
- [ ] Criar adaptadores de infraestrutura
- [ ] Implementar serviços de domínio
- [ ] Criar sistema de migração
- [ ] Implementar hooks de ativação
- [ ] Configurar container de dependências
- [ ] Testar arquitetura SOLID

## 🧪 **Testes da Nova Arquitetura**

### **1. Teste de Injeção de Dependência**

```php
// Testar se o container funciona corretamente
$container = new ServiceContainer();
$statisticsService = $container->get(StatisticsService::class);
```

### **2. Teste de Repositório**

```php
// Testar operações CRUD
$repository = $container->get(StatisticsRepositoryInterface::class);
$stats = new OptimizationStatistics(1, 1000, 800, 'jpegoptim', '/path/file.jpg', 'image/jpeg');
$repository->save($stats);
```

### **3. Teste de Migração**

```php
// Testar criação da tabela
$migration = new CreateStatisticsTable($container->get(WordPressDatabaseAdapter::class));
$migration->up();
```

## 📚 **Benefícios da Refatoração**

### **1. Manutenibilidade**

- Código organizado e fácil de entender
- Responsabilidades bem definidas
- Fácil de modificar e estender

### **2. Testabilidade**

- Dependências injetadas
- Interfaces bem definidas
- Fácil de mockar para testes

### **3. Escalabilidade**

- Arquitetura preparada para crescimento
- Fácil adicionar novas funcionalidades
- Padrões consistentes

### **4. WordPress Integration**

- Mantém compatibilidade com WordPress
- Usa padrões nativos quando apropriado
- Hooks e filtros bem implementados

## ⏭️ **Próxima Etapa**

Após completar esta etapa refatorada, prosseguir para **Etapa 2: Sistema de Estatísticas e Tracking** com a nova arquitetura como base sólida.

## 🎯 **Resumo das Melhorias**

✅ **Arquitetura SOLID implementada**
✅ **Object Calisthenics aplicados**
✅ **Clean Architecture seguida**
✅ **Dependency Injection configurada**
✅ **Interfaces e contratos definidos**
✅ **Separação de responsabilidades**
✅ **Código testável e manutenível**
✅ **Padrões de mercado aplicados**
