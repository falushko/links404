<?php

namespace AppBundle\Services;

use AppBundle\Entity\ExceptionLog;
use Doctrine\ORM\EntityManager;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This worker crawls website. To start consume message run: bin/console rabbitmq:consumer crawl &
 * Class CrawlerWorker
 * @package AppBundle\Services
 */
class CrawlerWorker
{
	private $crawler;
	private $em;

	public function __construct(Crawler $crawler, EntityManager $em)
	{
		$this->crawler = $crawler;
		$this->em = $em;
	}

	public function execute(AMQPMessage $message)
	{
		$body = unserialize($message->getBody());

		$website = $body['url'];
		$user = $body['user'];

		try {
			$this->crawler->crawl($website, $user);
		} catch (\Exception $e) {
			$exceptionLog = ExceptionLog::createFromException($e);
			$exceptionLog->url = $website;
			$this->em->persist($exceptionLog);
			$this->em->flush();
		}
	}
}