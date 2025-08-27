# 🎨 ETAPA 4: ASSETS E JAVASCRIPT REFATORADO

## 📋 **Objetivo da Etapa**

Implementar o sistema completo de assets (CSS, JavaScript) seguindo SOLID principles, Object Calisthenics e Clean Architecture, integrando com a nova arquitetura das Etapas 1, 2 e 3.

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

### **SOLID Principles:**

- ✅ **S**ingle Responsibility Principle
- ✅ **O**pen/Closed Principle
- ✅ **L**iskov Substitution Principle
- ✅ **I**nterface Segregation Principle
- ✅ **D**ependency Inversion Principle

## 📁 **Estrutura Refatorada**

```
wp-content/plugins/squidge/
├── core/
│   ├── Services/
│   │   ├── Contracts/
│   │   │   ├── AssetManagerInterface.php
│   │   │   ├── ThemeIntegrationInterface.php
│   │   │   └── PerformanceOptimizerInterface.php
│   │   ├── AssetManagerService.php
│   │   ├── ThemeIntegrationService.php
│   │   └── PerformanceOptimizerService.php
│   ├── ValueObjects/
│   │   ├── AssetBundle.php
│   │   ├── ThemeConfiguration.php
│   │   └── PerformanceMetrics.php
│   └── Exceptions/
│       └── AssetProcessingException.php
├── infrastructure/
│   └── WordPress/
│       ├── Assets/
│       │   ├── AssetEnqueuer.php
│       │   ├── AssetMinifier.php
│       │   └── AssetVersioning.php
│       └── Frontend/
│           ├── ScriptLocalizer.php
│           └── StyleCustomizer.php
├── assets/
│   ├── admin/
│   │   ├── css/
│   │   │   ├── admin.css
│   │   │   ├── dashboard.css
│   │   │   ├── batch-optimizer.css
│   │   │   └── statistics.css
│   │   ├── js/
│   │   │   ├── admin.js
│   │   │   ├── dashboard.js
│   │   │   ├── batch-optimizer.js
│   │   │   └── statistics.js
│   │   └── images/
│   │       ├── icons/
│   │       └── logos/
│   └── frontend/
│       ├── css/
│       └── js/
└── config/
    └── assets.php
```

## 🔧 **Implementação Refatorada**

### **1. Interface do Gerenciador de Assets**

```php
<?php
// core/Services/Contracts/AssetManagerInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\AssetBundle;

interface AssetManagerInterface
{
    public function registerAssetBundle(AssetBundle $bundle): void;
    public function enqueueAssetBundle(string $bundleName): void;
    public function dequeueAssetBundle(string $bundleName): void;
    public function getAssetUrl(string $assetPath): string;
    public function getAssetVersion(string $assetPath): string;
    public function optimizeAssets(): void;
}
```

### **2. Interface de Integração com Tema**

```php
<?php
// core/Services/Contracts/ThemeIntegrationInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\ThemeConfiguration;

interface ThemeIntegrationInterface
{
    public function detectTheme(): ThemeConfiguration;
    public function customizeAssets(ThemeConfiguration $theme): void;
    public function injectThemeSpecificCode(ThemeConfiguration $theme): void;
    public function validateThemeCompatibility(ThemeConfiguration $theme): bool;
}
```

### **3. Interface do Otimizador de Performance**

```php
<?php
// core/Services/Contracts/PerformanceOptimizerInterface.php

namespace Squidge\Services\Contracts;

use Squidge\ValueObjects\PerformanceMetrics;

interface PerformanceOptimizerInterface
{
    public function measureAssetPerformance(): PerformanceMetrics;
    public function optimizeAssetLoading(): void;
    public function implementLazyLoading(): void;
    public function optimizeCriticalPath(): void;
    public function getPerformanceReport(): array;
}
```

### **4. Value Object: Pacote de Assets**

```php
<?php
// core/ValueObjects/AssetBundle.php

namespace Squidge\ValueObjects;

class AssetBundle
{
    private string $name;
    private array $styles;
    private array $scripts;
    private array $dependencies;
    private string $version;
    private bool $inFooter;
    private array $localizeData;
    private string $media;

    public function __construct(
        string $name,
        array $styles = [],
        array $scripts = [],
        array $dependencies = [],
        string $version = '1.0.0',
        bool $inFooter = false,
        array $localizeData = [],
        string $media = 'all'
    ) {
        $this->name = $name;
        $this->styles = $styles;
        $this->scripts = $scripts;
        $this->dependencies = $dependencies;
        $this->version = $version;
        $this->inFooter = $inFooter;
        $this->localizeData = $localizeData;
        $this->media = $media;
    }

    public function addStyle(string $stylePath, array $dependencies = []): void
    {
        $this->styles[$stylePath] = $dependencies;
    }

    public function addScript(string $scriptPath, array $dependencies = []): void
    {
        $this->scripts[$scriptPath] = $dependencies;
    }

    public function addDependency(string $dependency): void
    {
        if (!in_array($dependency, $this->dependencies)) {
            $this->dependencies[] = $dependency;
        }
    }

    public function setLocalizeData(array $data): void
    {
        $this->localizeData = array_merge($this->localizeData, $data);
    }

    public function hasStyles(): bool
    {
        return !empty($this->styles);
    }

    public function hasScripts(): bool
    {
        return !empty($this->scripts);
    }

    public function getTotalAssets(): int
    {
        return count($this->styles) + count($this->scripts);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'dependencies' => $this->dependencies,
            'version' => $this->version,
            'in_footer' => $this->inFooter,
            'localize_data' => $this->localizeData,
            'media' => $this->media,
            'total_assets' => $this->getTotalAssets(),
        ];
    }

    // Getters
    public function getName(): string { return $this->name; }
    public function getStyles(): array { return $this->styles; }
    public function getScripts(): array { return $this->scripts; }
    public function getDependencies(): array { return $this->dependencies; }
    public function getVersion(): string { return $this->version; }
    public function getInFooter(): bool { return $this->inFooter; }
    public function getLocalizeData(): array { return $this->localizeData; }
    public function getMedia(): string { return $this->media; }
}
```

### **5. Value Object: Configuração do Tema**

```php
<?php
// core/ValueObjects/ThemeConfiguration.php

namespace Squidge\ValueObjects;

class ThemeConfiguration
{
    private string $name;
    private string $version;
    private string $textDomain;
    private array $supportedFeatures;
    private array $assetPaths;
    private bool $isChildTheme;
    private ?string $parentTheme;

    public function __construct(
        string $name,
        string $version = '1.0.0',
        string $textDomain = '',
        array $supportedFeatures = [],
        array $assetPaths = [],
        bool $isChildTheme = false,
        ?string $parentTheme = null
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->textDomain = $textDomain;
        $this->supportedFeatures = $supportedFeatures;
        $this->assetPaths = $assetPaths;
        $this->isChildTheme = $isChildTheme;
        $this->parentTheme = $parentTheme;
    }

    public function supportsFeature(string $feature): bool
    {
        return in_array($feature, $this->supportedFeatures);
    }

    public function hasCustomAssets(): bool
    {
        return !empty($this->assetPaths);
    }

    public function getAssetPath(string $type): ?string
    {
        return $this->assetPaths[$type] ?? null;
    }

    public function isModernTheme(): bool
    {
        return version_compare($this->version, '5.0.0', '>=');
    }

    public function requiresCustomIntegration(): bool
    {
        return $this->isChildTheme || $this->hasCustomAssets();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'text_domain' => $this->textDomain,
            'supported_features' => $this->supportedFeatures,
            'asset_paths' => $this->assetPaths,
            'is_child_theme' => $this->isChildTheme,
            'parent_theme' => $this->parentTheme,
            'is_modern_theme' => $this->isModernTheme(),
            'requires_custom_integration' => $this->requiresCustomIntegration(),
        ];
    }

    // Getters
    public function getName(): string { return $this->name; }
    public function getVersion(): string { return $this->version; }
    public function getTextDomain(): string { return $this->textDomain; }
    public function getSupportedFeatures(): array { return $this->supportedFeatures; }
    public function getAssetPaths(): array { return $this->assetPaths; }
    public function getIsChildTheme(): bool { return $this->isChildTheme; }
    public function getParentTheme(): ?string { return $this->parentTheme; }
}
```

### **6. Value Object: Métricas de Performance**

```php
<?php
// core/ValueObjects/PerformanceMetrics.php

namespace Squidge\ValueObjects;

class PerformanceMetrics
{
    private float $loadTime;
    private int $totalAssets;
    private int $totalSize;
    private array $assetLoadTimes;
    private float $firstContentfulPaint;
    private float $largestContentfulPaint;
    private \DateTime $measurementTime;

    public function __construct()
    {
        $this->loadTime = 0.0;
        $this->totalAssets = 0;
        $this->totalSize = 0;
        $this->assetLoadTimes = [];
        $this->firstContentfulPaint = 0.0;
        $this->largestContentfulPaint = 0.0;
        $this->measurementTime = new \DateTime();
    }

    public function setLoadTime(float $loadTime): void
    {
        $this->loadTime = $loadTime;
    }

    public function setTotalAssets(int $totalAssets): void
    {
        $this->totalAssets = $totalAssets;
    }

    public function setTotalSize(int $totalSize): void
    {
        $this->totalSize = $totalSize;
    }

    public function addAssetLoadTime(string $assetName, float $loadTime): void
    {
        $this->assetLoadTimes[$assetName] = $loadTime;
    }

    public function setFirstContentfulPaint(float $fcp): void
    {
        $this->firstContentfulPaint = $fcp;
    }

    public function setLargestContentfulPaint(float $lcp): void
    {
        $this->largestContentfulPaint = $lcp;
    }

    public function getAverageAssetLoadTime(): float
    {
        if (empty($this->assetLoadTimes)) {
            return 0.0;
        }

        $totalTime = array_sum($this->assetLoadTimes);
        return $totalTime / count($this->assetLoadTimes);
    }

    public function getTotalSizeInKB(): float
    {
        return round($this->totalSize / 1024, 2);
    }

    public function getTotalSizeInMB(): float
    {
        return round($this->totalSize / 1024 / 1024, 2);
    }

    public function getPerformanceScore(): int
    {
        $score = 100;

        if ($this->loadTime > 3.0) $score -= 20;
        if ($this->loadTime > 5.0) $score -= 30;
        if ($this->totalSize > 1024 * 1024) $score -= 15;
        if ($this->totalAssets > 20) $score -= 10;

        return max(0, $score);
    }

    public function toArray(): array
    {
        return [
            'load_time' => $this->loadTime,
            'total_assets' => $this->totalAssets,
            'total_size' => $this->totalSize,
            'total_size_kb' => $this->getTotalSizeInKB(),
            'total_size_mb' => $this->getTotalSizeInMB(),
            'asset_load_times' => $this->assetLoadTimes,
            'average_asset_load_time' => $this->getAverageAssetLoadTime(),
            'first_contentful_paint' => $this->firstContentfulPaint,
            'largest_contentful_paint' => $this->largestContentfulPaint,
            'performance_score' => $this->getPerformanceScore(),
            'measurement_time' => $this->measurementTime->format('Y-m-d H:i:s'),
        ];
    }

    // Getters
    public function getLoadTime(): float { return $this->loadTime; }
    public function getTotalAssets(): int { return $this->totalAssets; }
    public function getTotalSize(): int { return $this->totalSize; }
    public function getAssetLoadTimes(): array { return $this->assetLoadTimes; }
    public function getFirstContentfulPaint(): float { return $this->firstContentfulPaint; }
    public function getLargestContentfulPaint(): float { return $this->largestContentfulPaint; }
    public function getMeasurementTime(): \DateTime { return $this->measurementTime; }
}
```

### **7. Serviço Gerenciador de Assets**

```php
<?php
// core/Services/AssetManagerService.php

namespace Squidge\Services;

use Squidge\Services\Contracts\AssetManagerInterface;
use Squidge\ValueObjects\AssetBundle;
use Squidge\Infrastructure\WordPress\Assets\AssetEnqueuer;
use Squidge\Infrastructure\WordPress\Assets\AssetVersioning;

class AssetManagerService implements AssetManagerInterface
{
    private AssetEnqueuer $enqueuer;
    private AssetVersioning $versioning;
    private array $registeredBundles = [];

    public function __construct(
        AssetEnqueuer $enqueuer,
        AssetVersioning $versioning
    ) {
        $this->enqueuer = $enqueuer;
        $this->versioning = $versioning;
    }

    public function registerAssetBundle(AssetBundle $bundle): void
    {
        $this->registeredBundles[$bundle->getName()] = $bundle;

        $this->registerBundleStyles($bundle);
        $this->registerBundleScripts($bundle);
    }

    public function enqueueAssetBundle(string $bundleName): void
    {
        if (!isset($this->registeredBundles[$bundleName])) {
            throw new \InvalidArgumentException("Asset bundle '{$bundleName}' not found");
        }

        $bundle = $this->registeredBundles[$bundleName];

        $this->enqueuer->enqueueBundle($bundle);
        $this->localizeBundleData($bundle);
    }

    public function dequeueAssetBundle(string $bundleName): void
    {
        if (!isset($this->registeredBundles[$bundleName])) {
            return;
        }

        $bundle = $this->registeredBundles[$bundleName];

        $this->enqueuer->dequeueBundle($bundle);
    }

    public function getAssetUrl(string $assetPath): string
    {
        $pluginUrl = plugin_dir_url(SQUIDGE_PLUGIN_FILE);
        return $pluginUrl . 'assets/' . ltrim($assetPath, '/');
    }

    public function getAssetVersion(string $assetPath): string
    {
        return $this->versioning->getAssetVersion($assetPath);
    }

    public function optimizeAssets(): void
    {
        foreach ($this->registeredBundles as $bundle) {
            $this->optimizeBundle($bundle);
        }
    }

    private function registerBundleStyles(AssetBundle $bundle): void
    {
        foreach ($bundle->getStyles() as $stylePath => $dependencies) {
            $this->enqueuer->registerStyle(
                $bundle->getName() . '-' . basename($stylePath, '.css'),
                $this->getAssetUrl($stylePath),
                $dependencies,
                $bundle->getVersion(),
                $bundle->getMedia()
            );
        }
    }

    private function registerBundleScripts(AssetBundle $bundle): void
    {
        foreach ($bundle->getScripts() as $scriptPath => $dependencies) {
            $this->enqueuer->registerScript(
                $bundle->getName() . '-' . basename($scriptPath, '.js'),
                $this->getAssetUrl($scriptPath),
                $dependencies,
                $bundle->getVersion(),
                $bundle->getInFooter()
            );
        }
    }

    private function localizeBundleData(AssetBundle $bundle): void
    {
        $localizeData = $bundle->getLocalizeData();

        if (empty($localizeData)) {
            return;
        }

        foreach ($bundle->getScripts() as $scriptPath => $dependencies) {
            $scriptHandle = $bundle->getName() . '-' . basename($scriptPath, '.js');
            $this->enqueuer->localizeScript($scriptHandle, $bundle->getName() . 'Data', $localizeData);
        }
    }

    private function optimizeBundle(AssetBundle $bundle): void
    {
        $this->versioning->updateAssetVersion($bundle->getName());
    }
}
```

### **8. Enqueuer de Assets WordPress**

```php
<?php
// infrastructure/WordPress/Assets/AssetEnqueuer.php

namespace Squidge\Infrastructure\WordPress\Assets;

use Squidge\ValueObjects\AssetBundle;

class AssetEnqueuer
{
    private array $registeredStyles = [];
    private array $registeredScripts = [];

    public function registerStyle(
        string $handle,
        string $src,
        array $dependencies = [],
        string $version = '1.0.0',
        string $media = 'all'
    ): void {
        $this->registeredStyles[$handle] = [
            'src' => $src,
            'dependencies' => $dependencies,
            'version' => $version,
            'media' => $media,
        ];

        wp_register_style($handle, $src, $dependencies, $version, $media);
    }

    public function registerScript(
        string $handle,
        string $src,
        array $dependencies = [],
        string $version = '1.0.0',
        bool $inFooter = false
    ): void {
        $this->registeredScripts[$handle] = [
            'src' => $src,
            'dependencies' => $dependencies,
            'version' => $version,
            'in_footer' => $inFooter,
        ];

        wp_register_script($handle, $src, $dependencies, $version, $inFooter);
    }

    public function enqueueBundle(AssetBundle $bundle): void
    {
        $this->enqueueBundleStyles($bundle);
        $this->enqueueBundleScripts($bundle);
    }

    public function dequeueBundle(AssetBundle $bundle): void
    {
        $this->dequeueBundleStyles($bundle);
        $this->dequeueBundleScripts($bundle);
    }

    public function localizeScript(string $handle, string $objectName, array $data): void
    {
        wp_localize_script($handle, $objectName, $data);
    }

    private function enqueueBundleStyles(AssetBundle $bundle): void
    {
        foreach ($bundle->getStyles() as $stylePath => $dependencies) {
            $handle = $bundle->getName() . '-' . basename($stylePath, '.css');
            wp_enqueue_style($handle);
        }
    }

    private function enqueueBundleScripts(AssetBundle $bundle): void
    {
        foreach ($bundle->getScripts() as $scriptPath => $dependencies) {
            $handle = $bundle->getName() . '-' . basename($scriptPath, '.js');
            wp_enqueue_script($handle);
        }
    }

    private function dequeueBundleStyles(AssetBundle $bundle): void
    {
        foreach ($bundle->getStyles() as $stylePath => $dependencies) {
            $handle = $bundle->getName() . '-' . basename($stylePath, '.css');
            wp_dequeue_style($handle);
        }
    }

    private function dequeueBundleScripts(AssetBundle $bundle): void
    {
        foreach ($bundle->getScripts() as $scriptPath => $dependencies) {
            $handle = $bundle->getName() . '-' . basename($scriptPath, '.js');
            wp_dequeue_script($handle);
        }
    }
}
```

### **9. Sistema de Versionamento de Assets**

```php
<?php
// infrastructure/WordPress/Assets/AssetVersioning.php

namespace Squidge\Infrastructure\WordPress\Assets;

class AssetVersioning
{
    private string $pluginVersion;
    private array $assetVersions = [];

    public function __construct(string $pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
        $this->loadAssetVersions();
    }

    public function getAssetVersion(string $assetPath): string
    {
        $assetKey = $this->getAssetKey($assetPath);

        if (isset($this->assetVersions[$assetKey])) {
            return $this->assetVersions[$assetKey];
        }

        return $this->pluginVersion;
    }

    public function updateAssetVersion(string $assetName): void
    {
        $this->assetVersions[$assetName] = $this->generateAssetVersion();
        $this->saveAssetVersions();
    }

    public function generateAssetVersion(): string
    {
        return $this->pluginVersion . '.' . time();
    }

    private function getAssetKey(string $assetPath): string
    {
        return basename($assetPath);
    }

    private function loadAssetVersions(): void
    {
        $this->assetVersions = get_option('squidge_asset_versions', []);
    }

    private function saveAssetVersions(): void
    {
        update_option('squidge_asset_versions', $this->assetVersions);
    }
}
```

### **10. CSS Principal do Admin**

```css
/* assets/admin/css/admin.css */

/* Reset e Base */
.squidge-admin * {
	box-sizing: border-box;
}

.squidge-admin {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
		Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
	line-height: 1.6;
	color: #333;
}

/* Layout Principal */
.squidge-container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 20px;
}

.squidge-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 30px;
	border-radius: 10px;
	margin-bottom: 30px;
	text-align: center;
}

.squidge-header h1 {
	margin: 0;
	font-size: 2.5em;
	font-weight: 300;
}

.squidge-header p {
	margin: 10px 0 0 0;
	opacity: 0.9;
	font-size: 1.1em;
}

/* Cards e Seções */
.squidge-card {
	background: white;
	border-radius: 10px;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	padding: 25px;
	margin-bottom: 25px;
	border-left: 4px solid #667eea;
}

.squidge-card h2 {
	margin: 0 0 20px 0;
	color: #2c3e50;
	font-size: 1.5em;
	font-weight: 600;
}

/* Botões */
.squidge-btn {
	display: inline-block;
	padding: 12px 24px;
	background: #667eea;
	color: white;
	text-decoration: none;
	border-radius: 6px;
	border: none;
	cursor: pointer;
	font-size: 14px;
	font-weight: 500;
	transition: all 0.3s ease;
}

.squidge-btn:hover {
	background: #5a6fd8;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.squidge-btn-secondary {
	background: #95a5a6;
}

.squidge-btn-secondary:hover {
	background: #7f8c8d;
}

.squidge-btn-danger {
	background: #e74c3c;
}

.squidge-btn-danger:hover {
	background: #c0392b;
}

/* Formulários */
.squidge-form-group {
	margin-bottom: 20px;
}

.squidge-form-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: 500;
	color: #2c3e50;
}

.squidge-form-control {
	width: 100%;
	padding: 12px;
	border: 2px solid #e1e8ed;
	border-radius: 6px;
	font-size: 14px;
	transition: border-color 0.3s ease;
}

.squidge-form-control:focus {
	outline: none;
	border-color: #667eea;
	box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Tabelas */
.squidge-table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 20px;
}

.squidge-table th,
.squidge-table td {
	padding: 12px;
	text-align: left;
	border-bottom: 1px solid #e1e8ed;
}

.squidge-table th {
	background: #f8f9fa;
	font-weight: 600;
	color: #2c3e50;
}

.squidge-table tr:hover {
	background: #f8f9fa;
}

/* Status e Indicadores */
.squidge-status {
	display: inline-block;
	padding: 4px 12px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 500;
	text-transform: uppercase;
}

.squidge-status-success {
	background: #d4edda;
	color: #155724;
}

.squidge-status-warning {
	background: #fff3cd;
	color: #856404;
}

.squidge-status-error {
	background: #f8d7da;
	color: #721c24;
}

/* Responsividade */
@media (max-width: 768px) {
	.squidge-container {
		padding: 15px;
	}

	.squidge-header {
		padding: 20px;
	}

	.squidge-header h1 {
		font-size: 2em;
	}

	.squidge-card {
		padding: 20px;
	}
}
```

### **11. JavaScript Principal do Admin**

```javascript
// assets/admin/js/admin.js

(function ($) {
	"use strict";

	// Namespace principal do Squidge
	window.SquidgeAdmin = window.SquidgeAdmin || {};

	// Configurações globais
	SquidgeAdmin.config = {
		ajaxUrl: squidgeAdmin.ajaxUrl,
		nonce: squidgeAdmin.nonce,
		strings: squidgeAdmin.strings,
	};

	// Utilitários
	SquidgeAdmin.utils = {
		// Formata bytes para formato legível
		formatBytes: function (bytes, decimals = 2) {
			if (bytes === 0) return "0 Bytes";

			const k = 1024;
			const dm = decimals < 0 ? 0 : decimals;
			const sizes = ["Bytes", "KB", "MB", "GB", "TB"];

			const i = Math.floor(Math.log(bytes) / Math.log(k));

			return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
		},

		// Formata porcentagem
		formatPercentage: function (value) {
			return parseFloat(value).toFixed(2) + "%";
		},

		// Formata data
		formatDate: function (dateString) {
			const date = new Date(dateString);
			return date.toLocaleDateString() + " " + date.toLocaleTimeString();
		},

		// Validação de formulário
		validateForm: function (formElement) {
			const requiredFields = formElement.querySelectorAll("[required]");
			let isValid = true;

			requiredFields.forEach((field) => {
				if (!field.value.trim()) {
					field.classList.add("error");
					isValid = false;
				} else {
					field.classList.remove("error");
				}
			});

			return isValid;
		},

		// Exibe notificações
		showNotification: function (message, type = "info") {
			const notification = document.createElement("div");
			notification.className = `squidge-notification squidge-notification-${type}`;
			notification.innerHTML = `
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            `;

			document.body.appendChild(notification);

			// Auto-remove após 5 segundos
			setTimeout(() => {
				notification.remove();
			}, 5000);

			// Botão de fechar
			notification
				.querySelector(".notification-close")
				.addEventListener("click", () => {
					notification.remove();
				});
		},

		// Confirmação de ação
		confirmAction: function (message, callback) {
			if (confirm(message)) {
				callback();
			}
		},
	};

	// Sistema de AJAX
	SquidgeAdmin.ajax = {
		// Requisição GET
		get: function (action, data = {}) {
			return this.request("GET", action, data);
		},

		// Requisição POST
		post: function (action, data = {}) {
			return this.request("POST", action, data);
		},

		// Requisição genérica
		request: function (method, action, data) {
			const requestData = {
				action: action,
				nonce: SquidgeAdmin.config.nonce,
				...data,
			};

			return $.ajax({
				url: SquidgeAdmin.config.ajaxUrl,
				method: method,
				data: requestData,
				dataType: "json",
			});
		},

		// Tratamento de erros
		handleError: function (xhr, status, error) {
			console.error("AJAX Error:", { xhr, status, error });

			let errorMessage = "An error occurred";

			if (
				xhr.responseJSON &&
				xhr.responseJSON.data &&
				xhr.responseJSON.data.message
			) {
				errorMessage = xhr.responseJSON.data.message;
			}

			SquidgeAdmin.utils.showNotification(errorMessage, "error");
		},
	};

	// Sistema de eventos
	SquidgeAdmin.events = {
		// Registra um evento
		on: function (eventName, callback) {
			$(document).on(eventName, callback);
		},

		// Remove um evento
		off: function (eventName, callback) {
			$(document).off(eventName, callback);
		},

		// Dispara um evento
		trigger: function (eventName, data = {}) {
			$(document).trigger(eventName, data);
		},
	};

	// Inicialização
	SquidgeAdmin.init = function () {
		this.bindEvents();
		this.initializeComponents();
	};

	// Bind de eventos
	SquidgeAdmin.bindEvents = function () {
		// Eventos globais
		$(document).on("click", ".squidge-btn", function (e) {
			if ($(this).hasClass("disabled")) {
				e.preventDefault();
				return false;
			}
		});

		// Eventos de formulário
		$(document).on("submit", ".squidge-form", function (e) {
			if (!SquidgeAdmin.utils.validateForm(this)) {
				e.preventDefault();
				SquidgeAdmin.utils.showNotification(
					"Please fill in all required fields",
					"warning"
				);
				return false;
			}
		});

		// Eventos de tabela
		$(document).on("click", ".squidge-table th[data-sort]", function () {
			SquidgeAdmin.table.sortTable($(this));
		});
	};

	// Inicialização de componentes
	SquidgeAdmin.initializeComponents = function () {
		// Inicializa tooltips
		this.initializeTooltips();

		// Inicializa modais
		this.initializeModals();

		// Inicializa gráficos
		this.initializeCharts();
	};

	// Inicialização de tooltips
	SquidgeAdmin.initializeTooltips = function () {
		$("[data-tooltip]").each(function () {
			const $element = $(this);
			const tooltipText = $element.data("tooltip");

			$element.attr("title", tooltipText);
		});
	};

	// Inicialização de modais
	SquidgeAdmin.initializeModals = function () {
		$("[data-modal]").each(function () {
			const $element = $(this);
			const modalId = $element.data("modal");

			$element.on("click", function (e) {
				e.preventDefault();
				SquidgeAdmin.modal.open(modalId);
			});
		});
	};

	// Inicialização de gráficos
	SquidgeAdmin.initializeCharts = function () {
		$(".squidge-chart").each(function () {
			const $chart = $(this);
			const chartType = $chart.data("chart-type");
			const chartData = $chart.data("chart-data");

			if (chartData && window.Chart) {
				SquidgeAdmin.charts.createChart($chart[0], chartType, chartData);
			}
		});
	};

	// Sistema de modais
	SquidgeAdmin.modal = {
		open: function (modalId) {
			const $modal = $(`#${modalId}`);
			if ($modal.length) {
				$modal.addClass("active");
				$("body").addClass("modal-open");
			}
		},

		close: function (modalId) {
			const $modal = $(`#${modalId}`);
			if ($modal.length) {
				$modal.removeClass("active");
				$("body").removeClass("modal-open");
			}
		},
	};

	// Sistema de gráficos
	SquidgeAdmin.charts = {
		createChart: function (canvas, type, data) {
			const ctx = canvas.getContext("2d");

			new Chart(ctx, {
				type: type,
				data: data,
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: "bottom",
						},
					},
				},
			});
		},
	};

	// Sistema de tabelas
	SquidgeAdmin.table = {
		sortTable: function ($header) {
			const $table = $header.closest("table");
			const columnIndex = $header.index();
			const isAscending = $header.hasClass("sort-asc");

			// Remove classes de ordenação
			$table.find("th").removeClass("sort-asc sort-desc");

			// Adiciona classe de ordenação
			$header.addClass(isAscending ? "sort-desc" : "sort-asc");

			// Ordena a tabela
			this.sortTableByColumn($table, columnIndex, !isAscending);
		},

		sortTableByColumn: function ($table, columnIndex, ascending) {
			const $rows = $table.find("tbody tr").get();

			$rows.sort(function (a, b) {
				const aValue = $(a).find("td").eq(columnIndex).text();
				const bValue = $(b).find("td").eq(columnIndex).text();

				if (ascending) {
					return aValue.localeCompare(bValue);
				} else {
					return bValue.localeCompare(aValue);
				}
			});

			$.each($rows, function (index, row) {
				$table.find("tbody").append(row);
			});
		},
	};

	// Inicializa quando o DOM estiver pronto
	$(document).ready(function () {
		SquidgeAdmin.init();
	});
})(jQuery);
```

## ✅ **Checklist da Etapa 4 Refatorada**

- [ ] Implementar interfaces e contratos
- [ ] Criar value objects
- [ ] Implementar serviços de domínio
- [ ] Criar enqueuers de assets
- [ ] Implementar sistema de versionamento
- [ ] Criar CSS e JavaScript principais
- [ ] Integrar com arquitetura das Etapas 1, 2 e 3
- [ ] Testar funcionalidades
- [ ] Verificar princípios SOLID

## 🧪 **Testes da Nova Arquitetura**

### **1. Teste de Value Objects**

```php
// Testar criação e métodos dos value objects
$bundle = new AssetBundle('admin', ['admin.css'], ['admin.js']);
$themeConfig = new ThemeConfiguration('Twenty Twenty-Four');
$performanceMetrics = new PerformanceMetrics();
```

### **2. Teste de Serviços**

```php
// Testar gerenciamento de assets
$assetManager = $container->get(AssetManagerInterface::class);
$assetManager->registerAssetBundle($bundle);
```

### **3. Teste de Enqueuers**

```php
// Testar registro de assets
$enqueuer = new AssetEnqueuer();
$enqueuer->registerStyle('test-style', '/path/style.css');
```

## 📚 **Benefícios da Refatoração**

### **1. Arquitetura Limpa**

- Separação clara de responsabilidades
- Dependências injetadas
- Fácil de testar e manter

### **2. Princípios SOLID**

- Cada classe tem uma responsabilidade
- Interfaces bem definidas
- Fácil de estender

### **3. Object Calisthenics**

- Código limpo e legível
- Métodos pequenos e focados
- Sem aninhamento excessivo

### **4. Integração WordPress**

- Hooks e filtros bem implementados
- Sistema de assets organizado
- Versionamento automático

## ⏭️ **Próxima Etapa**

Após completar esta etapa refatorada, prosseguir para **Etapa 5: Integração Final e Testes** com a nova arquitetura como base sólida.

## 🎯 **Resumo das Melhorias**

✅ **Arquitetura SOLID implementada**
✅ **Object Calisthenics aplicados**
✅ **Value Objects criados**
✅ **Interfaces bem definidas**
✅ **Separação de responsabilidades**
✅ **Código testável e manutenível**
✅ **Integração com Etapas 1, 2 e 3**
✅ **Padrões de mercado aplicados**
