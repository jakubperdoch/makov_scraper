<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Bootstrap\Configurator();

// $configurator->setDebugMode('23.75.345.200'); // enable for your remote IP

if (($debug = getenv('DEBUG_MODE')) !== false) {
	$configurator->setDebugMode($debug === 'true');
}

$configurator->enableTracy(__DIR__ . '/../var/log');
$configurator->setTempDirectory(__DIR__ . '/../var');
$configurator->setTimeZone('Europe/Prague');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../config')
	->register();

$configurator->addConfig(__DIR__ . '/../config/config.neon');
$configurator->addConfig(__DIR__ . '/../config/config.local.neon');

return $configurator->createContainer();