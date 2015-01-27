<?php
/**
 * PSR-0 autoloader
 * @param string $className
 * @return bool
 */
spl_autoload_register(
	function ($className) {
		$pathLib = __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
		$pathPharLib = __DIR__ . DIRECTORY_SEPARATOR;

		$className = ltrim($className, '\\');
		$fileName = '';
		if ($lastNsPos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if (is_readable($path = $pathLib . $fileName)) {
			require_once $path;

			return true;
		} elseif (is_readable($path = $pathPharLib . $className . '.php')) { // Phar storage
			require_once $path;

			return true;
		}

		return false;
	},
	false
);
