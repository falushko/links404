<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    protected $errors;

    protected function createFromArray($entity, array $params)
    {
        $restrictedParams = ['id', 'password'];

        foreach($params as $key => $value) {
            if (in_array($key, $restrictedParams)) continue;
            if (!property_exists($entity, $key)) continue;

            $entity->$key = $value;
        }

        return $entity;
    }

    protected function isValid($entity, $group = 'default')
    {
        $errors = $this->get('validator')->validate($entity, null, $group);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                //from camel case to underscore
                $fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $error->getPropertyPath())), '_');
                $this->errors[$fieldName] = $error->getMessage();
            }

            return false;
        }

        return true;
    }

    protected function save($entities)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_array($entities)) {
            foreach ($entities as $entity) { $em->persist($entity); }
        } else {
            $em->persist($entities);
        }

        $em->flush();
    }
}