<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AppController
 * @package AppBundle\Controller
 */
class AppController extends Controller
{
    protected $errors;

    /**
     * Create entity from array. Array you can get from request's form.
     * @param $entity
     * @param array $params
     * @return mixed
     */
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

    /**
     * Validates entity based on constraints. If it is invalid it fills the field '$errors' and returns false.
     * @param $entity
     * @param string $group
     * @return bool
     */
    protected function isValid($entity, $group = 'default')
    {
        $errors = $this->get('validator')->validate($entity, null, $group);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                /** from camel case to underscore */
                $fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $error->getPropertyPath())), '_');
                $this->errors[$fieldName] = $error->getMessage();
            }

            return false;
        }

        return true;
    }

    /**
     * Just a helper for handier entities saving.
     * @param $entities
     */
    protected function save(...$entities)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($entities as $entity) { $em->persist($entity); }

        $em->flush();
    }
}