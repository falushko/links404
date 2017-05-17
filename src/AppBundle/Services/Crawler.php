<?php

namespace AppBundle\Services;

use ArrayIterator;
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

        $this->getAllWebsitePages($website);

        $result = [];

        $dom = new Dom;
        $dom->load($website);
        $links = $dom->find('a');

        foreach($links as $link) {
            $link = $link->tag->getAttribute('href')['value'];
            $media = $this->isLinkToMedia($link);
            $broken = $this->isMediaDoesNotExist($link);
            $outbound = $this->isLinkOutbound($link, $website);
            $status = $this->getHTTPResponseStatus($link);
        }
    }

    /**
     * @param string $website
     * @return array
     */
    public function getAllWebsitePages(string $website) : array
    {
        $pages = [];

        $client = new HTTPClient();
        $response = $client->get($website . '/sitemhap.xml', ['exceptions' => false]);

        if ($response->getStatusCode() === 200) {
            $links = new SimpleXMLElement($response->getBody());

            foreach ($links as $link) $pages[] = (string) $link->loc;

            dump($pages, '1'); exit();
        } else {

            $pages[] = $website;
            $counter = 0;

            while (true) {
                if ($counter >= count($pages)) break;

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
            }


            dump($pages, '2'); exit();

        }

//
//        $arr = array();
//        $arr['b'] = 'book';
//
//        $array_iterator = new ArrayIterator($arr);
//
//
//        foreach($array_iterator as $key=>$val) {
//            print "key=>$key\n";
//            if(!isset($array_iterator['a']))
//                $array_iterator['a'] = 'apple';
//        }



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
        return (strpos($link, $website) !== false) ? false : true;
    }

    /**
     * Get response status for link.
     * @param string $link
     * @return array
     */
    public function getHTTPResponseStatus(string $link) : array
    {
        $client = new HTTPClient();
        $response = $client->request('GET', $link);

        return [
            'code' => $response->getStatusCode(),
            'phrase' => $response->getReasonPhrase()
        ];
    }

    //todo add broken images checker

    /**
     * Check if link is picture, video or mp3.
     * @param string $link
     * @return bool
     */
    public function isLinkToMedia(string $link) : bool
    {
        $medias = ['.jpeg', '.jpg', '.gif', '.png', '.flv', '.mp3', '.mp4'];

        foreach ($medias as $media) {
            /** Check if link ends with $medias */
            if (substr_compare($link, $media, strlen($link)-strlen($media), strlen($media)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if link to media is broken.
     * @param string $link
     * @return bool
     */
    public function isMediaDoesNotExist(string $link) : bool
    {
        return $this->getHTTPResponseStatus($link)['code'] !== 200 ? true : false;
    }
}