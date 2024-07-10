<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\Services;

final class PostFindService
{
	/**
	 * @return array<string>
	 */
	public function execute(PostFindRequest $request): array
	{
		// load source page
		// find all posts and their urls
		// return array of post urls

		// @todo remove - example only
		return [
			'https://portly-octagon.info',
			'https://muted-rail.name',
			'https://black-and-white-tip.biz',
			'https://ideal-dining.org'
		];
	}
}