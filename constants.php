<?php

/**
 * Constantes do Plugin Squidge
 *
 * Este arquivo documenta todas as constantes disponíveis no plugin
 *
 * @package Squidge
 * @version 0.1.4
 */

// Constantes principais definidas em squidge.php
defined('SQUIDGE_VERSION') || define('SQUIDGE_VERSION', '0.1.4');
defined('SQUIDGE_PLUGIN_FILE') || define('SQUIDGE_PLUGIN_FILE', __FILE__);
defined('SQUIDGE_PLUGIN_DIR') || define('SQUIDGE_PLUGIN_DIR', plugin_dir_path(__FILE__));
defined('SQUIDGE_PLUGIN_URL') || define('SQUIDGE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Constantes de diretórios
defined('SQUIDGE_TEMPLATE_PATH') || define('SQUIDGE_TEMPLATE_PATH', SQUIDGE_PLUGIN_DIR . 'templates');
defined('SQUIDGE_ASSETS_URL') || define('SQUIDGE_ASSETS_URL', SQUIDGE_PLUGIN_URL . 'assets');
defined('SQUIDGE_CORE_PATH') || define('SQUIDGE_CORE_PATH', SQUIDGE_PLUGIN_DIR . 'core');
defined('SQUIDGE_INFRASTRUCTURE_PATH') || define('SQUIDGE_INFRASTRUCTURE_PATH', SQUIDGE_PLUGIN_DIR . 'infrastructure');

// Constantes de upload (definidas dinamicamente)
// SQUIDGE_UPLOAD_DIR - Diretório de upload do WordPress
// SQUIDGE_UPLOAD_URL - URL de upload do WordPress

/**
 * Lista de constantes disponíveis:
 *
 * SQUIDGE_VERSION              - Versão do plugin
 * SQUIDGE_PLUGIN_FILE          - Caminho completo do arquivo principal
 * SQUIDGE_PLUGIN_DIR           - Diretório raiz do plugin
 * SQUIDGE_PLUGIN_URL           - URL base do plugin
 * SQUIDGE_TEMPLATE_PATH        - Caminho para templates
 * SQUIDGE_ASSETS_URL           - URL para assets (CSS/JS)
 * SQUIDGE_CORE_PATH            - Caminho para classes core
 * SQUIDGE_INFRASTRUCTURE_PATH  - Caminho para infraestrutura
 *
 * Exemplo de uso:
 * - CSS: SQUIDGE_ASSETS_URL . '/admin/admin.css'
 * - JS: SQUIDGE_ASSETS_URL . '/admin/admin.js'
 * - Templates: SQUIDGE_TEMPLATE_PATH . '/admin/dashboard.php'
 * - Classes: SQUIDGE_CORE_PATH . '/Services/BatchOptimizationService.php'
 */
