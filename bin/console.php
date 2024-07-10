<?php
declare(strict_types=1);

use Nette\DI\Container;
use Symfony\Component\Console\Application;

/** @var Container $container */
$container = require __DIR__ . '/../src/bootstrap.php';
$console = $container->getByType(Application::class);

exit($console->run());