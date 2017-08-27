<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BrokenLink;
use AppBundle\Entity\ExceptionLog;
use AppBundle\Entity\Feedback;
use AppBundle\Form\FeedbackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class MainController extends AppController
{
	/**
	 * @Route("/", name="homepage")
	 * @Method({"GET", "POST"})
	 * @Template
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|void
	 */
    public function indexAction(Request $request)
	{
		if ($request->getMethod() == 'GET') return;

		$url = $request->get('url');

		try {
			$links = $this->get('app.crawler')->crawl($url);
		} catch (\Exception $e) {
			$exceptionLog = ExceptionLog::createFromException($e);
			$exceptionLog->url = $url;
			$this->save($exceptionLog);

			return $this->render('@App/main/exception.html.twig');
		}

		foreach ($links['brokenLinks'] as $link) {
			$brokenLink = new BrokenLink();
			$brokenLink->host = $url;
			$brokenLink->link = $link['link'];
			$brokenLink->page = $link['page'];
			$brokenLink->status = $link['status'];
			$brokenLink->isMedia = false;

			$this->get('em')->persist($brokenLink);
		}

		foreach ($links['brokenMedia'] as $link) {
			$brokenLink = new BrokenLink();
			$brokenLink->host = $url;
			$brokenLink->link = $link['link'];
			$brokenLink->page = $link['page'];
			$brokenLink->status = $link['status'];
			$brokenLink->isMedia = true;

			$this->get('em')->persist($brokenLink);
		}

		$this->get('em')->flush();

		return $this->redirectToRoute('result', ['url' => $url]);
	}

    /**
     * @Route("/about", name="about")
     * @Method({"GET"})
     * @Template
     */
    public function aboutAction(){}

	/**
	 * @Route("/result", name="result")
	 * @Method({"GET"})
	 * @Template
	 * @param Request $request
	 * @return array|\Symfony\Component\HttpFoundation\Response
	 */
    public function resultAction(Request $request)
    {
    	$host = $request->get('url');

    	$links = $this->get('em')
			->getRepository('AppBundle:BrokenLink')
			->findByHost($host);

		return ['links' => $links, 'host' => $host];
    }

	/**
	 * @Route("/contacts", name="contacts")
	 * @Method({"GET", "POST"})
	 * @Template
	 * @param Request $request
	 * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
	 */
    public function contactsAction(Request $request)
    {
		$feedback = new Feedback();
		$form = $this->createForm(FeedbackType::class, $feedback);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->save($feedback);
			$this->addFlash('success', 'successful_feedback');
			return $this->redirectToRoute('contacts');
		}

		return ['form' => $form->createView()];
    }

	/**
	 * @Route("/language/{language}", name="language")
	 * @Method({"GET"})
	 * @param Request $request
	 * @param $language
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function languageAction(Request $request, $language)
	{
		$this->get('session')->set('language', $language);

		return $this->redirect($request->headers->get('referer'));
	}
}
