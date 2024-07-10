<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\DI;

use Nette\DI\CompilerExtension;

final class GalileoExtension extends CompilerExtension
{
	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$this->compiler->loadDefinitionsFromConfig(
			$this->loadFromFile(__DIR__ . '/config.neon')
		);
	}
}