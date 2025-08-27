<?php

/**
 * Autoloader personalizado para o plugin Squidge
 * Carrega apenas as classes do nosso plugin, evitando conflitos com Carbon Fields
 */

spl_autoload_register(function ($class) {
	// Verifica se a classe pertence ao namespace Squidge
	if (strpos($class, 'Squidge\\') !== 0) {
		return;
	}

	// Remove o namespace Squidge\ do início
	$relativeClass = substr($class, 8);

	// Converte namespace em caminho de arquivo
	$file = SQUIDGE_CORE_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';

	// Verifica se o arquivo existe e o carrega
	if (file_exists($file)) {
		require_once $file;
	}
});
