<?php

namespace AppBundle\Services;

use GuzzleHttp\Client as HTTPClient;
use PHPHtmlParser\Dom;
use SimpleXMLElement;

/**
 * Moves through website, finds all pages and outbound links. Checks http response status codes.
 * Class Crawler
 * @package AppBundle\Services
 */
class Crawler
{
    /**
     * Main method that makes the shit done.
     * @param $website
     * @return array
     */
    public function crawl(string $website) : array
    {
        $brokenMediaLinks = [];
        $brokenLinksWithStatuses = [];
        $pages = $this->getAllWebsitePages($website);

        dump($pages); exit();

        foreach ($pages as $page) {

            $dom = new Dom;
            $dom->load($page);
            $links = $dom->find('a');

            foreach ($links as $link) {
                $link = $link->tag->getAttribute('href')['value'];

                if ($this->isLinkToMedia($link)) {
                    if ($this->isMediaNotExist($link)) continue;

                    $brokenMediaLinks[] = ['page' => $page, 'link' => $link];
                } else {

                    try {
                        $status = $this->getHTTPResponseStatus($link);

                        if ($status['code'] === 200) continue;

                        $brokenLinksWithStatuses[] = ['page' => $page, 'link' => $link, 'status' => $status];
                    } catch (\Exception $exception) {

                    }
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

            try {
                $dom = new Dom;
                $dom->load($pages[$counter]);
                $links = $dom->find('a');

                foreach ($links as $link) {
                    $link = $link->tag->getAttribute('href')['value'];

                    if ($this->isLinkOutbound($link, $website)) continue;
                    if ($this->isLinkToMedia($link)) continue;
                    if (in_array($link, $pages)) continue;

                    $pages[] = $link;
                }

                $counter++;

            } catch (\Exception $exception) {

            }

        }

        return $pages;
    }

    /**
     * Get all website pages from sitemap.xml. Only one level sitemap is allowed.
     * @param string $website
     * @return array
     * @throws \Exception
     */
    public function getAllWebsitePagesFromSitemap(string $website) : array
    {
        $pages = [];
        $client = new HTTPClient();
        $response = $client->get($website . '/sitemap.xml', ['exceptions' => false]);

        //todo create custom exception and handle it
        if ($response->getStatusCode() !== 200) throw new \Exception();

        $links = new SimpleXMLElement($response->getBody());

        foreach ($links as $link) $pages[] = (string) $link->loc;

        return $pages;
    }

    /**
     * Get all inbound links from page.
     * @param string $page
     * @param string $website
     * @return array
     */
    public function getAllInboundLinksFromPage(string $page, string $website) : array
    {
        $result = [];



        return $result;
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
        $response = $client->head($link, ['exceptions' => false]);

        return [
            'code' => $response->getStatusCode(),
            'phrase' => $response->getReasonPhrase()
        ];
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
                if (substr_compare($link, $media, strlen($link) - strlen($media), strlen($media)) === 0) {
                    return true;
                }

            } catch (\Exception $exception) {

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