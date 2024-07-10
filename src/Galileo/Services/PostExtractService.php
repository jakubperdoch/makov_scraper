<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Services;

use Brosland\Extractor\Galileo\Domain\PostDto;
use DateTimeImmutable;

final readonly class PostExtractService
{
	public function execute(PostExtractRequest $request): PostDto
	{
		sleep(1); // @todo remove

		// load post page
		// parse data
		// return PostDto

		// @todo replace
		return new PostDto(
			sourceUrl: $request->url,
			title: 'Title',
			previewImage: null,
			annotation: '',
			content: '',
			createdAt: new DateTimeImmutable(),
			updatedAt: new DateTimeImmutable(),
			photos: [],
			attachments: []
		);
	}
}