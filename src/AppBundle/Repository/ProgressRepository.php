<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Progress;
use Doctrine\ORM\EntityRepository;

class ProgressRepository extends EntityRepository
{
	public function updateProgress($user, $website, $current, $count)
	{
		$progress = $this->getOneOrNew($user, $website);
		$progress->current = $current;
		$progress->count = $count;
		$this->_em->persist($progress);
		$this->_em->flush();
	}

	public function getProgress($user, $website)
	{
		$progress = $this->getOneOrNew($user, $website);

		$current = $progress->current ?? 0;
		$count = $progress->count ?? 1;
		$result = round($current / $count * 100);

		if ($result == 100) {
			$progress->current = 0;
			$progress->count = 0;
			$this->_em->persist($progress);
			$this->_em->flush();
		}

		return $result;
	}

	public function getOneOrNew($user, $website)
	{
		$userUrl = $user . '|' . $website;

		$progress = $this->createQueryBuilder('p')
			->select('p')
			->where('p.userUrl = :userUrl')
			->setParameter('userUrl', $userUrl)
			->getQuery()
			->getOneOrNullResult();

		if (!$progress) {
			$progress = new Progress();
			$progress->userUrl = $userUrl;
		}

		return $progress;
	}
}
