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
     * @Assert\NotBlank(groups={"default"})
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(groups={"default"})
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.", groups={"default"})
     */
    public $email;

    /**
     * @ORM\Column(type="string", length=2000)
     * @Assert\NotBlank(groups={"default"})
     * @Assert\Length(
     *      min = 3,
     *      max = 2000,
     *      minMessage = "Message must be at least {{ limit }} characters long",
     *      maxMessage = "Message cannot be longer than {{ limit }} characters"
     * )
     */
    public $message;
}