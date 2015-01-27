<?php
/**
 * Mad MD. Markdown CMS.
 *
 * (c) Dmitry Zhiltsov <info@zhiltsov.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(0);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

try {
	if (!isset($_SERVER['argv'][1]) || !$iniFile = $_SERVER['argv'][1]) {
		throw new \Exception('Не указан ini файл конфигурации');
	}

	$iniFile = realpath($iniFile);
	if (!file_exists($iniFile)) {
		throw new \Exception('Файл конфигурации не найден');
	}

	\SiteMap\SiteManager::loadConfig($iniFile);
	\SiteMap\SiteManager::run();
} catch (\Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}
