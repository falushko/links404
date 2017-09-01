<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="statistics")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StatisticRepository")
 */
class Statistic
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string")
	 */
	public $website;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $pagesAmount;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $analysisTime;

	/**
	 * @ORM\Column(type="datetime")
	 */
	public $createdAt;

	/**
	 * @ORM\Column(type="datetime")
	 */
	public $updatedAt;

	public function __construct($website)
	{
		$this->website = $website;
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}

	/**
	 * @ORM\PreUpdate()
	 */
	public function setUpdatedAtValue()
	{
		$this->updatedAt = new \DateTime();
	}

	public function getAnalysisTimeFormatted()
	{
		return gmdate("H:i:s", $this->analysisTime);
	}
}