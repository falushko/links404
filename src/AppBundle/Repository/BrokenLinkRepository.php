<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BrokenLinkRepository extends EntityRepository
{
	public function findByHost($host)
	{
		return $this->createQueryBuilder('bl')
			->select('bl')
			->where('bl.host = :host')
			->setParameter('host', $host)
			->orderBy('bl.isMedia', 'DESC')
			->orderBy('bl.status')
			->getQuery();
	}
}