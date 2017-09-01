<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsRepository")
 */
class News
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
	public $title;

	/**
	 * @ORM\Column(type="text")
	 */
	public $body;

	/**
	 * @ORM\Column(type="string", length=5)
	 */
	public $language;

	/**
	 * @ORM\Column(type="datetime")
	 */
	public $createdAt;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}
}