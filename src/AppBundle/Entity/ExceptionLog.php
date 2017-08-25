<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exception_logs")
 * @ORM\Entity()
 */
class ExceptionLog
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $code;

	/**
	 * @ORM\Column(type="string")
	 */
	public $message;

	/**
	 * @ORM\Column(type="text")
	 */
	public $stackTrace;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $url;

	/**
	 * @ORM\Column(type="datetime")
	 */
	public $createdAt;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}

	public static function createFromException(\Exception $e)
	{
		$exceptionLog = new ExceptionLog();
		$exceptionLog->code = $e->getCode();
		$exceptionLog->message = $e->getMessage();
		$exceptionLog->stackTrace = $e->getTraceAsString();

		return $exceptionLog;
	}
}