<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Translation\Translator;

/**
 * This listener changes translations on twig templates.
 * Class LocaleListener
 * @package AppBundle\Listener
 */
class LocaleListener
{
	protected $translator;
	protected $session;

	public function __construct(Translator $translator, Session $session)
	{
		$this->translator = $translator;
		$this->session = $session;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		$language = $this->session->get('language', 'en');
		$this->translator->setLocale($language);
	}
}