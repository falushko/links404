<?php

namespace AppBundle\Services;

use AppBundle\Entity\BrokenLink;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportGenerator
{
    public function getCsvReport($data)
    {
        $response = new StreamedResponse();
        $response->setCallback(function() use ($data) {

            $handle = fopen('php://output', 'w+');
            fputcsv($handle, ['Page', 'Link', 'Status'], ';');

            /** @var BrokenLink $row */
            foreach ($data as $row)
                fputcsv($handle, [$row->page, $row->link, $row->status], ';');

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="report.csv"');

        return $response;
    }
}