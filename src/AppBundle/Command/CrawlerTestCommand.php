<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlerTestCommand extends ContainerAwareCommand
{
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$website = $input->getArgument('url');

		$this->getContainer()->get('app.crawler')->crawl($website, 'some_user');
	}

	protected function configure()
	{
		$this->setName('crawler:test')
			->setDescription('Greet someone')
			->addArgument('url', InputArgument::REQUIRED);
	}
}