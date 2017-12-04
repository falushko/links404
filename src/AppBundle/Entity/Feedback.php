<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="feedbacks")
 * @ORM\Entity()
 */
class Feedback
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=2000)
     * @Assert\NotBlank()
     * @Assert\Length(min = 3, max = 2000)
     */
    public $message;

	/**
	 * @ORM\Column(type="datetime")
	 */
    public $createdAt;

    public function __construct()
	{
		$this->createdAt = new \DateTime();
	}
}