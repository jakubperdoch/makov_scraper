<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Services;

use Brosland\Extractor\Galileo\Domain\PostDto;

interface PostExportProvider
{
	/**
	 * @param array<PostDto> $posts
	 */
	function export(string $outputFileName, array $posts): void;
}