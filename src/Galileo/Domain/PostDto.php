<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Domain;

use DateTimeImmutable;

final readonly class PostDto
{
	/**
	 * @param array<FileDto> $photos
	 * @param array<FileDto> $attachments
	 */
	public function __construct(
		public string $sourceUrl,
		public string $title,
		public ?FileDto $previewImage,
		public string $annotation,
		public string $content,
		public DateTimeImmutable $createdAt,
		public DateTimeImmutable $updatedAt,
		public array $photos,
		public array $attachments
	)
	{
	}
}