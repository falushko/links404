<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Feedback;
use AppBundle\Services\Sitemap;
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
//        $links = $this->get('app.crawler')->crawl('https://bablo.click');


        $sitemap = new Sitemap();

        //игнорировать ссылки с расширениями:
        $sitemap->set_ignore(["javascript:", ".css", ".js", ".ico", ".jpg", ".png", ".jpeg", ".swf", ".gif"]);
        $sitemap->get_links("http://hand-build.ru");
        $links = $sitemap->get_array();


        dump($links); exit();
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
        if ($request->getMethod() === 'GET') return;

        $feedback = $this->createFromArray(new Feedback(), $request->request->all());

        if (!$this->isValid($feedback)) return [
            'errors' => $this->errors,
            'fields' => $request->request->all()
        ];

        $this->save($feedback);
        $this->addFlash('success', 'Thank\'s for your feedback!');

        return $this->redirectToRoute('contacts');
    }
}
