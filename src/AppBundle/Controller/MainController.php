<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Feedback;
use AppBundle\Entity\Progress;
use AppBundle\Form\FeedbackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MainController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 * @Method({"GET", "POST"})
	 * @Template
	 * @param Request $request
	 * @return array|string|JsonResponse
     */
    public function indexAction(Request $request)
	{
		if ($request->getMethod() == 'GET') return [];

		$this->get('enqueue.producer')->sendEvent('crawler', [
            'url' => $request->get('url'),
            'user' =>  $this->get('session')->get('user')
        ]);

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
	 * @return array|Response
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
     * @Route("/csv", name="csv")
     * @Method({"GET"})
     * @param Request $request
     * @return StreamedResponse
     */
    public function csvAction(Request $request)
    {
        $data = $this->getDoctrine()->getRepository('AppBundle:BrokenLink')->findAllByHost($request->get('url'));
        $response = $this->get('app.report.generator')->getCsvReport($data);

        return $response;
    }

	/**
	 * @Route("/contacts", name="contacts")
	 * @Method({"GET", "POST"})
	 * @Template
	 * @param Request $request
	 * @return array|RedirectResponse
	 */
    public function contactsAction(Request $request)
    {
		$feedback = new Feedback();
		$form = $this->createForm(FeedbackType::class, $feedback);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('em')->persist($feedback);
			$this->get('em')->flush();

            $this->get('enqueue.producer')->sendEvent('mailer', serialize($feedback));
			$this->addFlash('success', 'successful_feedback');
			return $this->redirectToRoute('contacts');
		}

		return ['form' => $form->createView()];
    }

	/**
	 * @Route("/progress", name="progress")
	 * @Method({"GET"})
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function getProgressAction(Request $request)
	{
		$user = $this->get('session')->get('user');
		$website = $request->get('url');

		$progress = $this->getDoctrine()
			->getRepository('AppBundle:Progress')
			->getProgress($user, $website);

		if ($progress == 0) {
			$result = ['progress' => Progress::STARTED];
		} elseif ($progress == 100) {
			$result = [
				'url' => $this->generateUrl('result', ['url' => $request->get('url')]),
				'progress' => Progress::FINISHED];
		} else {
			$result = [
				'progressPercentage' => $progress,
				'progress' => Progress::IN_PROGRESS];
		}

		return new JsonResponse($result);
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
