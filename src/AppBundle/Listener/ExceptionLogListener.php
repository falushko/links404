<?php

namespace AppBundle\Listener;

use AppBundle\Entity\ExceptionLog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ExceptionLogListener
{
	private $mailerProducer;

	/**
	 * FeedbackListener constructor.
	 * @param $mailerProducer
	 */
	public function __construct(Producer $mailerProducer)
	{
		$this->mailerProducer = $mailerProducer;
	}

	/**
	 * Send email with feedback to support
	 * @param LifecycleEventArgs $args
	 */
	public function postPersist(LifecycleEventArgs $args)
	{
		$exceptionLog = $args->getEntity();

		if (!$exceptionLog instanceof ExceptionLog) return;

		$this->mailerProducer->publish(serialize($exceptionLog));
	}
}