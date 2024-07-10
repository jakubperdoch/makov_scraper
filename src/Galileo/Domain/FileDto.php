<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Domain;

final readonly class FileDto
{
	public function __construct(
		public string $sourceUrl,
		public string $name,
		public string $description,
		public string $contentType,
		public string $ext,
		public int $size
	)
	{
	}
}