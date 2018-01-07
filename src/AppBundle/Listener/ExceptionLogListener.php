<?php

namespace AppBundle\Listener;

use AppBundle\Entity\ExceptionLog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enqueue\Client\Producer;

class ExceptionLogListener
{
	private $producer;

	/**
	 * FeedbackListener constructor.
	 * @param $producer
	 */
	public function __construct(Producer $producer)
	{
		$this->producer = $producer;
	}

	/**
	 * Send email with feedback to support
	 * @param LifecycleEventArgs $args
	 */
	public function postPersist(LifecycleEventArgs $args)
	{
		$exceptionLog = $args->getEntity();

		if ($exceptionLog instanceof ExceptionLog)
			$this->producer->sendEvent('mailer', serialize($exceptionLog));
	}
}