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
		$user = $input->getArgument('user');

		$this->getContainer()->get('app.crawler')->crawl($website, $user);
	}

	protected function configure()
	{
		$this->setName('crawler:test')
			->setDescription('Greet someone')
			->addArgument('url', InputArgument::REQUIRED)
			->addArgument('user', InputArgument::REQUIRED);
	}
}