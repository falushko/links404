<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Predis\Client as Redis;

/**
 * This service controls analysis progress.
 * Class AnalysisProgress
 * @package AppBundle\Services
 */
class AnalysisProgress
{
	const STARTED = 'started';
	const IN_PROGRESS = 'inProgress';
	const FINISHED = 'finished';

	private $session;
	private $redis;

	public function __construct(Session $session, Redis $redis)
	{
		$this->session = $session;
		$this->redis = $redis;
	}

	/**
	 * Updates progress for specific user and url.
	 * @param $url
	 * @param $user
	 * @param $current
	 * @param $count
	 */
	public function updateProgress($url, $user, $current, $count)
	{
		$this->redis->hset($user.'|'.$url, 'current', $current);
		$this->redis->hset($user.'|'.$url, 'count', $count);
	}

	/**
	 * Gets progress for specific user and url.
	 * @param $url
	 * @return float
	 */
	public function getProgress($url)
	{
		$user = $this->session->get('user');
		$current = $this->redis->hget($user.'|'.$url, 'current');
		$count = $this->redis->hget($user.'|'.$url, 'count');
		$result = round($current / $count * 100);

		if ($result == 100) {
			$this->redis->hset($user.'|'.$url, 'current', 0);
			$this->redis->hset($user.'|'.$url, 'count', 0);
		}

		return $result;
	}
}