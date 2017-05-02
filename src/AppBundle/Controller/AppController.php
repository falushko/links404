<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    protected function createFromArray(string $entityName, array $params)
    {
        $restrictedParams = ['id', 'password'];

        $entity = new $entityName;

        foreach($params as $key => $value) {
            if (in_array($key, $restrictedParams)) continue;
            if (!property_exists($entity, $key)) continue;

            $entity->$key = $value;
        }

        return $entity;
    }
}