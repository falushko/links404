<?php

namespace AppBundle\Workers;

use Interop\Queue\PsrMessage;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrProcessor;
use Enqueue\Client\TopicSubscriberInterface;
use AppBundle\Entity\ExceptionLog;
use AppBundle\Services\Crawler;
use Doctrine\ORM\EntityManager;

/**
 * This worker crawls website. To start consume message run: bin/console enqueue:consume --setup-broker
 * Class CrawlerWorker
 * @package AppBundle\Services
 */
class CrawlerWorker implements PsrProcessor, TopicSubscriberInterface
{
    private $crawler;
    private $em;

    public function __construct(Crawler $crawler, EntityManager $em)
    {
        $this->crawler = $crawler;
        $this->em = $em;
    }

    public function process(PsrMessage $message, PsrContext $session)
    {
        $website = json_decode($message->getBody())->url;
        $user = json_decode($message->getBody())->user;

        try {
            $this->crawler->crawl($website, $user);
        } catch (\Exception $e) {
            $exceptionLog = ExceptionLog::createFromException($e);
            $exceptionLog->url = $website;
            $this->em->persist($exceptionLog);
            $this->em->flush();
        }

        return self::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['crawler'];
    }
}