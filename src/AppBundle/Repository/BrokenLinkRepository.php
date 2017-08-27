<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BrokenLinkRepository extends EntityRepository
{
	public function findByHost($host)
	{
		$brokenLink = $this->createQueryBuilder('bl')
			->select('bl')
			->where('bl.host = :host')
			->andWhere('bl.isMedia = false')
			->setParameter('host', $host)
			->orderBy('bl.status')
			->getQuery()
			->getResult();

		$brokenMedia = $this->createQueryBuilder('bl')
			->select('bl')
			->where('bl.host = :host')
			->andWhere('bl.isMedia = true')
			->setParameter('host', $host)
			->orderBy('bl.status')
			->getQuery()
			->getResult();

		return ['brokenLink' => $brokenLink, 'brokenMedia' => $brokenMedia];
	}
}