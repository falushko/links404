<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {

    }

    /**
     * @Route("/result", name="result")
     * @Template
     */
    public function resultAction()
    {

    }

    /**
     * @Route("/about", name="about")
     * @Template
     */
    public function aboutAction()
    {

    }

    /**
     * @Route("/contacts", name="contacts")
     * @Template
     */
    public function contactsAction()
    {

    }
}
