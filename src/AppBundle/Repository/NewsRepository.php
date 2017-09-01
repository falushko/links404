<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository
{
	public function getAllQuery($language)
	{
		return $this->createQueryBuilder('n')
			->select('n')
			->where('n.language = :language')
			->setParameter('language', $language)
			->orderBy('n.createdAt', 'DESC')
			->getQuery();
	}
}