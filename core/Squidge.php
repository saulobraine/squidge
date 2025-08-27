<?php

/**
 * Squidge
 *
 * Main squidge class.
 *
 * @package     Squidge
 * @version     0.1.4
 * @author      Ainsley Clark
 * @category    Class
 * @repo        https://github.com/ainsleyclark/squidge
 *
 */

namespace Squidge;

use Squidge\Admin;
use function Clue\StreamFilter\fun;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

final class Squidge
{
	/**
	 * The plugin version number.
	 *
	 * @var string
	 */
	var $version = '0.1.1';

	/**
	 * The instance of Squidge.
	 *
	 * @var null
	 */
	private static $instance = NULL;

	/**
	 * Boots Squidge and initialises the
	 * static instance, once.
	 *
	 * @return Squidge|null
	 * @date 24/11/2021
	 * @since 0.1.0
	 */
	public static function boot()
	{
		if (self::$instance == null) {
			$squidge = new Squidge();
			$squidge->initialize();
			self::$instance = $squidge;
		}
		return self::$instance;
	}

	/**
	 * Sets up the Squidge plugin.
	 *
	 * @param void
	 * @return void
	 * @since 0.1.2
	 * @date 05/12/2021
	 */
	private function initialize()
	{
		// As constantes já estão definidas no arquivo principal
		$uploadData = wp_upload_dir();
		$this->define('SQUIDGE_UPLOAD_DIR', $uploadData['basedir']);
		$this->define('SQUIDGE_UPLOAD_URL', $uploadData['baseurl']);

		// Inicializar funcionalidades administrativas
		$this->initializeAdminMenu();

		new Admin\Fields();
		add_action('carbon_fields_fields_registered', function () {
			new Admin\Upload();
		});
	}

	/**
	 * Inicializar menu administrativo
	 */
	private function initializeAdminMenu(): void
	{
		add_action('admin_menu', [$this, 'addAdminMenu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
	}

	/**
	 * Adicionar menu administrativo
	 */
	public function addAdminMenu(): void
	{
		// Menu principal
		add_menu_page(
			'Squidge Dashboard',
			'Squidge',
			'manage_options',
			'squidge-dashboard',
			[$this, 'renderDashboardPage'],
			'dashicons-images-alt2',
			30
		);

		// Submenu Dashboard
		add_submenu_page(
			'squidge-dashboard',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'squidge-dashboard',
			[$this, 'renderDashboardPage']
		);

		// Submenu Estatísticas
		add_submenu_page(
			'squidge-dashboard',
			'Estatísticas',
			'Estatísticas',
			'manage_options',
			'squidge-statistics',
			[$this, 'renderStatisticsPage']
		);

		// Submenu Otimizador em Lote
		add_submenu_page(
			'squidge-dashboard',
			'Otimizador em Lote',
			'Otimizador em Lote',
			'manage_options',
			'squidge-batch-optimizer',
			[$this, 'renderBatchOptimizerPage']
		);

		// Submenu Configurações
		add_submenu_page(
			'squidge-dashboard',
			'Configurações',
			'Configurações',
			'manage_options',
			'squidge-settings',
			[$this, 'renderSettingsPage']
		);
	}

	/**
	 * Carregar assets administrativos
	 */
	public function enqueueAdminAssets(string $hook): void
	{
		if (strpos($hook, 'squidge') === false) {
			return;
		}

		wp_enqueue_style(
			'squidge-admin',
			SQUIDGE_ASSETS_URL . '/admin/admin.css',
			[],
			SQUIDGE_VERSION
		);

		wp_enqueue_script(
			'squidge-admin',
			SQUIDGE_ASSETS_URL . '/admin/admin.js',
			['jquery'],
			SQUIDGE_VERSION,
			true
		);
	}

	/**
	 * Renderizar página do Dashboard
	 */
	public function renderDashboardPage(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('Você não tem permissões suficientes para acessar esta página.'));
		}

		$this->renderAdminPage('dashboard');
	}

	/**
	 * Renderizar página de Estatísticas
	 */
	public function renderStatisticsPage(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('Você não tem permissões suficientes para acessar esta página.'));
		}

		$this->renderAdminPage('statistics');
	}

	/**
	 * Renderizar página do Otimizador em Lote
	 */
	public function renderBatchOptimizerPage(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('Você não tem permissões suficientes para acessar esta página.'));
		}

		$this->renderAdminPage('batch-optimizer');
	}

	/**
	 * Renderizar página de Configurações
	 */
	public function renderSettingsPage(): void
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('Você não tem permissões suficientes para acessar esta página.'));
		}

		$this->renderAdminPage('settings');
	}

	/**
	 * Renderizar página administrativa genérica
	 */
	private function renderAdminPage(string $pageName): void
	{
		$templatePath = SQUIDGE_TEMPLATE_PATH . '/admin/' . $pageName . '.php';

		if (file_exists($templatePath)) {
			include $templatePath;
			// Adicionar classe CSS para indicar que a página foi carregada
			echo '<script>jQuery(document).ready(function() { jQuery(".wrap").addClass("squidge-page-loaded"); });</script>';
		} else {
			echo '<div class="wrap">';
			echo '<h1>' . ucfirst($pageName) . '</h1>';
			echo '<p>Esta página está em desenvolvimento.</p>';
			echo '</div>';
		}
	}

	/**
	 * Defines a constant if doesnt already exist.
	 *
	 * @param string $name The constant name.
	 * @param mixed $value The constant value.
	 * @return void
	 * @since 0.1.0
	 * @date    24/11/2021
	 *
	 */
	function define($name, $value = true)
	{
		if (!defined($name)) {
			define($name, $value);
		}
	}
}
