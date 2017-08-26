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
     * @Assert\NotBlank(message = "name_blank")
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message = "email_blank")
     * @Assert\Email(message = "email_invalid")
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=2000)
     * @Assert\NotBlank(message = "message_blank")
     * @Assert\Length(
     *      min = 3,
     *      max = 2000,
     *      minMessage = "message_short",
     *      maxMessage = "message_long",
     * )
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