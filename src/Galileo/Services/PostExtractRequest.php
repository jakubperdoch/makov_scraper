<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Services;

final readonly class PostExtractRequest
{
	public function __construct(
		public string $url
	)
	{
	}
}