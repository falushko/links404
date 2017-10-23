<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="broken_links")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BrokenLinkRepository")
 */
class BrokenLink
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
	public $host;

	/**
	 * @ORM\Column(type="string")
	 */
	public $page;

	/**
	 * @ORM\Column(type="string")
	 */
	public $link;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $status;

	/**
	 * @ORM\Column(type="datetime")
	 */
	public $createdAt;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}
}