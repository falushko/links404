<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Statistic;
use Doctrine\ORM\EntityRepository;

class StatisticRepository extends EntityRepository
{
	/**
	 * @param $website
	 * @return Statistic|object
	 */
	public function findOneByWebsiteOrCreateNew($website)
	{
		$statistic = $this->findOneBy(['website' => $website]);

		return $statistic ?? new Statistic($website);
	}
}