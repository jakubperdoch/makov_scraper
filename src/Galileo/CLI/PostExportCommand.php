<?php
declare(strict_types=1);

namespace Brosland\Extractor\Galileo\CLI;

use Brosland\Extractor\Galileo\Services\PostExportProvider;
use Brosland\Extractor\Galileo\Services\PostExtractRequest;
use Brosland\Extractor\Galileo\Services\PostExtractService;
use Brosland\Extractor\Galileo\Services\PostFindRequest;
use Brosland\Extractor\Galileo\Services\PostFindService;
use Exception;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

#[AsCommand(
	name: 'galileo:posts:export',
	description: 'Export posts to JSON.'
)]
final class PostExportCommand extends Command
{
	public function __construct(
		private readonly PostExportProvider $postExportProvider,
		private readonly PostExtractService $postExtractService,
		private readonly PostFindService $postFindService
	)
	{
		parent::__construct();
	}

	public function configure(): void
	{
		parent::configure();

		$this->addArgument(
			name: 'sourceUrl',
			mode: InputArgument::REQUIRED,
			description: 'The source url of the post list.'
		);
		$this->addArgument(
			name: 'outputFileName',
			mode: InputArgument::REQUIRED,
			description: 'The output file name.'
		);
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		if (!$output instanceof ConsoleOutputInterface) {
			throw new LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
		}

		/** @var array<string,mixed> $args */
		$args = $input->getArguments();

		try {
			$findRequest = new PostFindRequest($args['sourceUrl']);
			$postUrlList = $this->postFindService->execute($findRequest);

			$output->writeln(sprintf('%d posts found', count($postUrlList)));

			$processSection = $output->section();
			$posts = [];

			foreach ($postUrlList as $postUrl) {
				$message = sprintf('Processing %d/%d - %s', count($posts) + 1, count($postUrlList), $postUrl);
				$processSection->overwrite($message);

				$extractRequest = new PostExtractRequest($postUrl);
				$posts[] = $this->postExtractService->execute($extractRequest);
			}

			$this->postExportProvider->export($args['outputFileName'], $posts);

			$processSection->overwrite(sprintf('%d posts exported.', count($posts)));

			return 0; // zero return code means everything is ok
		} catch (Exception $ex) {
			Debugger::log($ex);

			$output->writeln('<error>' . $ex->getMessage() . '</error>');

			return 1; // non-zero return code means error
		}
	}
}