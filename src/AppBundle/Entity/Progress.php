<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="progress")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProgressRepository")
 */
class Progress
{
	const STARTED = 'started';
	const IN_PROGRESS = 'inProgress';
	const FINISHED = 'finished';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="user_url", type="string", length=255, unique=true)
     */
	public $userUrl;

    /**
     * @ORM\Column(name="current", type="integer")
     */
	public $current;

    /**
     * @ORM\Column(name="count", type="integer")
     */
	public $count;
}

