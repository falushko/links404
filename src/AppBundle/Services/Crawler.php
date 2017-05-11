<?php

namespace AppBundle\Services;

/**
 * Class Crawler
 * @package AppBundle\Services
 * Moves through website, finds all pages and outbound links. Checks http response status codes.
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

    }

    /**
     * Check if link is outbound.
     * @param string $link
     * @return bool
     */
    private function isLinkOutbound(string $link) : bool
    {

    }

    /**
     * Get response code for link.
     * @param string $link
     */
    private function getHTTPResponseCode(string $link)
    {

    }
}