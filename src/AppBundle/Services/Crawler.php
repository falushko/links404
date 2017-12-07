<?php

namespace AppBundle\Services;

use AppBundle\Entity\BrokenLink;
use AppBundle\Entity\ExceptionLog;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as HTTPClient;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\CurlException;

/**
 * Moves through website, finds all pages and outbound links. Checks http response status codes.
 * Class Crawler
 * @package AppBundle\Services
 */
class Crawler
{
	private $em;
	private $progress;
	private $ignoredLinks = [
		'https://t.me/',
		'https://telegram.me/',
		'http://vk.com/',
		'https://vk.com/',
		'whatsapp://',
		'mailto:',
		'javascript',
		'?reply',
		'https://metrika.yandex.ru/',
	];

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->progress = $em->getRepository('AppBundle:Progress');
	}

	/**
	 * Main method that makes the things done.
	 * @param string $website
	 * @param $user
	 * @return array
	 */
    public function crawl(string $website, $user) : array
    {
    	// todo there are a cases with links like href='Interview' need to add slash at the beginning if needed

		$start = time();

		set_time_limit(0);
		$this->progress->updateProgress($user, $website, 0, 1);
        $brokenLinks = [];
        $checkedLinks = [];
        $pages = $this->getAllWebsitePages($website);
		$currentPageNumber = 0;

        foreach ($pages as $page) {
        	$this->progress->updateProgress($user, $website, $currentPageNumber, count($pages));
			$dom = new Dom;

			try {
				$dom->load($page);
			} catch (\Exception $exception) {
				$exceptionLog = ExceptionLog::createFromException($exception);
				$exceptionLog->url = $page;
				$this->em->persist($exceptionLog);
				$this->em->flush();
				$currentPageNumber++;
				continue;
			}

            $links = $dom->find('a');

            dump('page:', $page);

            foreach ($links as $link) {
                $link = $link->tag->getAttribute('href')['value'];
				$link = $this->trimAnchor($link);
				$link = $this->addHostIfNeeded($link, $website);

				dump('link:', $link);

				if ($this->isLinkIgnored($link)) continue;
				if (in_array($link, $checkedLinks)) continue;

				$index = array_search($link, array_column($brokenLinks, 'link'));

				if ($index && $brokenLinks[$index]['page'] == $page) {
					continue;
				} elseif ($index) {
					$brokenLinks[] = ['page' => $page, 'link' => $link, 'status' => $brokenLinks[$index]['status']];
					continue;
				}

				$status = $this->getHTTPResponseStatus($link);

				if ($status['code'] === 200) {
					$checkedLinks[] = $link;
					continue;
				} else {
					$brokenLinks[] = ['page' => $page, 'link' => $link, 'status' => $status['code']];
				}
            }

            $currentPageNumber++;
        }

        $this->addBrokenLinksToDb($website, $brokenLinks);

		$end = time();
		$this->saveStatistic($website, count($pages), $end - $start);
		$this->progress->updateProgress($user, $website, $currentPageNumber, count($pages));

        return $brokenLinks;
    }

    /**
     * Get all website pages.
     * @param string $website
     * @return array
     */
    public function getAllWebsitePages(string $website) : array
    {
    	// todo try to optimize algorithm
        $pages[] = $website;
        $counter = 0;

        while (true) {
            if ($counter >= count($pages)) break;

			$dom = new Dom;

			try {
				$dom->load($pages[$counter]);
				$links = $dom->find('a');

				foreach ($links as $link) {
					$link = $link->tag->getAttribute('href')['value'];

					if ($this->isLinkIgnored($link)) continue;

					$link = $this->trimAnchor($link);
					$link = $this->addHostIfNeeded($link, $website);
					$link = $this->trimReplyToComment($link);

					if ($this->isLinkOutbound($link, $website)) continue;
					if ($this->isLinkToMedia($link)) continue;
					if (in_array($link, $pages)) continue;
					if (empty($link)) continue;

					$pages[] = $link;
				}
			} catch (CurlException $exception) {
				$exceptionLog = ExceptionLog::createFromException($exception);
				$exceptionLog->url = $pages[$counter];
				$this->em->persist($exceptionLog);
				$this->em->flush();
			}

			$counter++;
        }

        return $pages;
    }

	/**
	 * Adds broken links to db, previously deletes all links for domain.
	 * @param $host
	 * @param $brokenLinks
	 */
    private function addBrokenLinksToDb($host, $brokenLinks)
	{
		$this->em->createQueryBuilder()
			->delete('AppBundle:BrokenLink', 'bl')
			->where('bl.host = :host')
			->setParameter('host', $host)
			->getQuery()
			->execute();

		foreach ($brokenLinks as $link) {
			$brokenLink = new BrokenLink();
			$brokenLink->host = $host;
			$brokenLink->link = $link['link'];
			$brokenLink->page = $link['page'];
			$brokenLink->status = $link['status'];
			$this->em->persist($brokenLink);
		}

		$this->em->flush();
	}

	public function saveStatistic($website, $pagesAmount, $executionTime)
	{
		$statistic = $this->em->getRepository('AppBundle:Statistic')->findOneByWebsiteOrCreateNew($website);
		$statistic->pagesAmount = $pagesAmount;
		$statistic->analysisTime = $executionTime;
		$this->em->persist($statistic);
		$this->em->flush($statistic);
	}

	/**
	 * Adds host to link if it is absent
	 * @param $link
	 * @param $host
	 * @return mixed
	 */
    public function addHostIfNeeded($link, $host)
	{
		if (strpos($link, "http") === 0) return $link;

		return (strpos($link, $host) === false) ? rtrim($host, '/') . $link : $link;
	}

	/**
	 * Checks if link is ignored
	 * @param $link
	 * @return bool
	 */
	private function isLinkIgnored($link)
	{
		foreach ($this->ignoredLinks as $ignoredLink) {
			if (strpos($link, $ignoredLink) !== false) return true;
		}

		return false;
	}

	/**
	 * Trims replytocom query param
	 * @param $link
	 * @return mixed
	 */
	private function trimReplyToComment($link)
	{
		$explodedLink = explode('?', $link);

		return isset($explodedLink[1]) && strpos($explodedLink[1], 'replytocom') === 0
			? $explodedLink[0]
			: $link;
	}

	/**
	 * Trim anchor that goes after # symbol
	 * @param $link
	 * @return mixed
	 */
    public function trimAnchor($link)
	{
		return preg_match("/#(.*)$/", $link) ? explode('#', $link)[0] : $link;
	}

    /**
     * Check if link is outbound.
     * @param string $link
     * @param string $website
     * @return bool
     */
    public function isLinkOutbound(string $link, string $website) : bool
    {
        return !(strpos($link, $website) === 0);
    }

    /**
     * Get response status for link.
     * @param string $link
     * @return array
     */
    public function getHTTPResponseStatus(string $link) : array
    {
        $client = new HTTPClient();

		try {
			$response = $client->head($link, [
				'exceptions' => false,
				'timeout' => 5,
				'connect_timeout' => 5
			]);
			return ['code' => $response->getStatusCode(), 'phrase' => $response->getReasonPhrase()];
		} catch (\Exception $e) {
			return ['code' => 404, 'phrase' => 'Host does not exist.'];
		}
    }

    /**
     * Check if link is picture, video or mp3.
     * @param string $link
     * @return bool
     */
    public function isLinkToMedia(string $link) : bool
    {
        $medias = ['.jpeg', '.jpg', '.gif', '.png', '.flv', '.mp3', '.mp4'];

        foreach ($medias as $media) {

            try {
                /** Check if link ends with $medias */
                if (substr_compare($link, $media, strlen($link) - strlen($media), strlen($media)) === 0) return true;

            } catch (\Exception $e) {
				$exceptionLog = ExceptionLog::createFromException($e);
				$this->em->persist($exceptionLog);
				$this->em->flush();
            }
        }

        return false;
    }

    /**
     * Check if link to media is broken.
     * @param string $link
     * @return bool
     */
    public function isMediaNotExist(string $link) : bool
    {
        return $this->getHTTPResponseStatus($link)['code'] !== 200 ? true : false;
    }
}
