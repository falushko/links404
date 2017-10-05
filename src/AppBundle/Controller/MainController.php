<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Feedback;
use AppBundle\Form\FeedbackType;
use AppBundle\Services\AnalysisProgress;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class MainController extends Controller
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

		$this->get('app.crawler.producer')->publish(serialize([
			'url' => $request->get('url'),
			'user' =>  $this->get('session')->get('user')
		]));

		return new JsonResponse();
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
			$this->get('em')->persist($feedback);
			$this->get('em')->flush();

			$this->get('app.mailer.producer')->publish(serialize($feedback));
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
			->getAllQuery($this->get('session')->get('language', 'en'));

		$pagination = $this->get('knp_paginator')
			->paginate($newsQuery, $request->query->getInt('page', 1), 20);

		return ['pagination' => $pagination];
	}

	/**
	 * @Route("/progress", name="progress")
	 * @Method({"GET"})
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function getProgressAction(Request $request)
	{
		$progress = $this->get('app.analysis_progress')->getProgress($request->get('url'));

		if ($progress == 0) {
			$result = ['progress' => AnalysisProgress::STARTED];
		} elseif ($progress == 100) {
			$result = [
				'url' => $this->generateUrl('result', ['url' => $request->get('url')]),
				'progress' => AnalysisProgress::FINISHED];
		} else {
			$result = [
				'progressPercentage' => $progress,
				'progress' => AnalysisProgress::IN_PROGRESS];
		}

		return new JsonResponse($result);
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

	/**
	 * @Route("/login", name="login")
	 * @Template()
	 * @Method({"GET", "POST"})
	 * @return array
	 */
	public function loginAction()
	{
		return [
			'last_username' => $this->get('security.authentication_utils')->getLastUsername(),
			'error'  => $this->get('security.authentication_utils')->getLastAuthenticationError()
		];
	}
}
