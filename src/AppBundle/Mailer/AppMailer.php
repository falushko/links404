<?php

namespace AppBundle\Mailer;

use AppBundle\Entity\Feedback;
use Swift_Message;
use Symfony\Component\DependencyInjection\Container;

class AppMailer
{
    const FEEDBACK_MAIL = 'signal@checkmyart.com';

    private $container;

    public function __construct(Container $container)
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