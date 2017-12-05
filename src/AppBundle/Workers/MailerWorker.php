<?php

namespace AppBundle\Workers;

use AppBundle\Entity\ExceptionLog;
use AppBundle\Entity\Feedback;
use AppBundle\Services\Mailer;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrProcessor;
use Enqueue\Client\TopicSubscriberInterface;

/**
 * It is mailer consumer. To start consume message run: bin/console enqueue:consume --setup-broker
 * Class Mailer
 * @package AppBundle\Services
 */
class MailerWorker implements PsrProcessor, TopicSubscriberInterface
{
	private $mailer;

	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

    public function process(PsrMessage $message, PsrContext $session)
    {
        // todo implement this shit
        $website = json_decode($message->getBody())->url;
        $user = json_decode($message->getBody())->user;

        $message = unserialize($message->getBody());

        if ($message instanceof Feedback)
            $this->mailer->sendFeedbackMessage($message);
        elseif ($message instanceof ExceptionLog)
            $this->mailer->sendExceptionLogMessage($message);

        return self::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['mailer'];
    }
}