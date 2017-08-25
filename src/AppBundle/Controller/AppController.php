<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AppController
 * @package AppBundle\Controller
 */
class AppController extends Controller
{
    /**
     * Just a helper for handier entities saving.
     * @param $entities
     */
    protected function save(...$entities)
    {
        $em = $this->get('em');

        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->flush();
    }
}