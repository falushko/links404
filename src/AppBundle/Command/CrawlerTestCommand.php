<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlerTestCommand extends ContainerAwareCommand
{
	private $sitesPool = [];

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		foreach ($this->sitesPool as $site)
			$this->getContainer()->get('app.crawler')->crawl($site, 'test-user');
	}

	protected function configure()
	{
		$this->setName('crawler:test')->setDescription('Test crawl');
	}
}