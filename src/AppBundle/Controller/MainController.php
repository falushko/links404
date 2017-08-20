<?php

namespace AppBundle\Controller;

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
     * @Method({"GET"})
     * @Template
     */
    public function indexAction(){}

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
     */
    public function resultAction()
    {
        $links = $this->get('app.crawler')->crawl('https://website.com');
        //todo implement
    }

    /**
     * @Route("/contacts", name="contacts")
     * @Method({"GET", "POST"})
     * @Template
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function contactsAction(Request $request)
    {
		$feedback = new Feedback();
		$form = $this->createForm(FeedbackType::class, $feedback);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->save($feedback);
			$this->addFlash('success', 'Thank\'s for your feedback!');
			return $this->redirectToRoute('contacts');
		}

		return ['form' => $form->createView()];
    }
}
