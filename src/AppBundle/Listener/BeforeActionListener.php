<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Translation\Translator;

/**
 * This listener sets unique session id for each user.
 * Class LocaleListener
 * @package AppBundle\Listener
 */
class BeforeActionListener
{
	protected $session;
	protected $translator;

	public function __construct(Session $session, Translator $translator)
	{
		$this->session = $session;
		$this->translator = $translator;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		$user = $this->session->get('user');
		if (!$user) $this->session->set('user', uniqid ('user_id_', true));

		$language = $this->session->get('language', 'en');
		$this->translator->setLocale($language);
	}
}