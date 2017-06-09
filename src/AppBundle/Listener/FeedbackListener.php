<?php

namespace AppBundle\Listener;

use AppBundle\Entity\Feedback;
use Doctrine\ORM\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class FeedbackListener
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
        $feedback = $args->getEntity();

        if (!$feedback instanceof Feedback) return;

        $this->mailerProducer->publish(serialize($feedback));
    }
}