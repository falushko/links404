<?php

namespace AppBundle\Services;

use AppBundle\Entity\ExceptionLog;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as HTTPClient;
use PHPHtmlParser\Dom;

/**
 * Moves through website, finds all pages and outbound links. Checks http response status codes.
 * Class Crawler
 * @package AppBundle\Services
 */
class Crawler
{
	private $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
     * Main method that makes the things done.
     * @param $website
     * @return array
     */
    public function crawl(string $website) : array
    {
        $brokenMediaLinks = [];
        $brokenLinksWithStatuses = [];
        $pages = $this->getAllWebsitePages($website);

        foreach ($pages as $page) {
			$dom = new Dom;
            $dom->load($page);
            $links = $dom->find('a');

            foreach ($links as $link) {
                $link = $link->tag->getAttribute('href')['value'];
				$link = $this->trimAnchor($link);
				$link = $this->addHostIfNeeded($link, $website);

                if ($this->isLinkToMedia($link)) {
                    if (!$this->isMediaNotExist($link)) continue;

                    $brokenMediaLinks[] = ['page' => $page, 'link' => $link];
                } else {
					$status = $this->getHTTPResponseStatus($link);

					if ($status['code'] === 200) continue;

					$brokenLinksWithStatuses[] = ['page' => $page, 'link' => $link, 'status' => $status];
                }
            }
        }

        return ['brokenLinks' => $brokenLinksWithStatuses, 'brokenMedia' => $brokenMediaLinks];
    }

    /**
     * Get all website pages.
     * @param string $website
     * @return array
     */
    public function getAllWebsitePages(string $website) : array
    {
        $pages[] = $website;
        $counter = 0;

        while (true) {
            if ($counter >= count($pages)) break;

			$dom = new Dom;
			$dom->load($pages[$counter]);
			$links = $dom->find('a');

			foreach ($links as $link) {
				$link = $link->tag->getAttribute('href')['value'];
				$link = $this->trimAnchor($link);
				$link = $this->addHostIfNeeded($link, $website);

				if ($this->isLinkOutbound($link, $website)) continue;
				if ($this->isLinkToMedia($link)) continue;
				if (in_array($link, $pages)) continue;
				if (empty($link)) continue;

				$pages[] = $link;
			}

			$counter++;
        }

        return $pages;
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
        return strpos($link, $website) !== false || strpos($link, "http") !== 0 ? false : true;
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
			$response = $client->head($link, ['exceptions' => false]);

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
