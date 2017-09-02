<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ExceptionLog;
use AppBundle\Entity\Feedback;
use AppBundle\Form\FeedbackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
	 * @return string
	 */
    public function indexAction(Request $request)
	{
		if ($request->getMethod() == 'GET') return;

		$url = $request->get('url');

		try {
			$this->get('app.crawler')->crawl($url);
		} catch (\Exception $e) {
			$exceptionLog = ExceptionLog::createFromException($e);
			$exceptionLog->url = $url;
			$this->save($exceptionLog);

			return $this->render('@App/main/exception.html.twig');
		}

		return new JsonResponse($this->generateUrl('result', ['url' => $url]));
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
    	$statistic = $this->getDoctrine()->getRepository('AppBundle:Statistic')->findOneBy(['website' => $host]);
    	$brokenLinkQuery = $this->getDoctrine()->getRepository('AppBundle:BrokenLink')->findByHost($host);
		$pagination = $this->get('knp_paginator')->paginate($brokenLinkQuery, $request->query->getInt('page', 1), 20);

		return ['pagination' => $pagination, 'host' => $host, 'statistic' => $statistic];
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
	 * @Route("/news", name="news")
	 * @Method({"GET"})
	 * @Template
	 * @param Request $request
	 * @return array
	 */
    public function newsAction(Request $request)
	{
		$newsQuery = $this->getDoctrine()
			->getRepository('AppBundle:News')
			->getAllQuery($this->get('session')->get('language'));

		$pagination = $this->get('knp_paginator')
			->paginate($newsQuery, $request->query->getInt('page', 1), 20);

		return ['pagination' => $pagination];
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
