<?php

namespace AppBundle\Workers;

use AppBundle\Entity\ExceptionLog;
use AppBundle\Entity\Feedback;
use AppBundle\Services\Mailer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * It is mailer consumer. To start consume message run: bin/console rabbitmq:consumer mail &
 * Class Mailer
 * @package AppBundle\Services
 */
class MailerWorker implements ConsumerInterface
{
	private $mailer;

	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

    public function execute(AMQPMessage $message)
    {
        $message = unserialize($message->getBody());

		if ($message instanceof Feedback) {
			$this->mailer->sendFeedbackMessage($message);
		} elseif ($message instanceof ExceptionLog) {
			$this->mailer->sendExceptionLogMessage($message);
		}
    }
}