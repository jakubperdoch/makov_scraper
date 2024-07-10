<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Infrastructure;

use Brosland\Extractor\Galileo\Domain\PostDto;
use Brosland\Extractor\Galileo\Services\PostExportProvider;

final readonly class JsonPostExportProvider implements PostExportProvider
{
	/**
	 * @param array<PostDto> $posts
	 */
	public function export(string $outputFileName, array $posts): void
	{
//		throw new RuntimeException('Not implemented');
	}
}