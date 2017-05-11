<?php

namespace AppBundle\Services;

use AppBundle\Entity\Feedback;
use Swift_Message;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mailer
{
    const FEEDBACK_MAIL = 'signal@checkmyart.com';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendFeedbackMail(Feedback $feedback)
    {
        $body = $this->container->get('twig')
            ->render('@App/emails/activation.html.twig', [
                'link' => $user->getActivationCode(),
            ]);

        $this->container->get('mailer')->send(Swift_Message::newInstance()
            ->setSubject('404links feedback')
            ->setFrom('vov8278@gmail.com')
            ->setTo(self::FEEDBACK_MAIL)
            ->setBody($body, 'text/html')
        );
    }
}