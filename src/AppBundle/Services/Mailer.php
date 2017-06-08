<?php

namespace AppBundle\Services;

use AppBundle\Entity\Feedback;
use Swift_Message;

class Mailer
{
    private $twig;
    private $mailer;
    const FEEDBACKS_EMAIL = '404links@gmail.com';

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function sendFeedbackMail(Feedback $feedback)
    {
        $body = $this->twig
            ->render('@App/mails/feedback.html.twig', [
                'name' => $feedback->name,
                'email' => $feedback->email,
                'body' => $feedback->message]);

        $this->mailer->send(Swift_Message::newInstance()
            ->setSubject('404links feedback')
            ->setFrom($feedback->email)
            ->setTo(self::FEEDBACKS_EMAIL)
            ->setBody($body, 'text/html')
        );
    }
}