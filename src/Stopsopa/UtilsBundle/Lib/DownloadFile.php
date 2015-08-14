<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use DateTime;

class DownloadFile
{
    /**
     *
     * Uwaga warty zainteresowania przy dużych plikach jest też StreamResponse http://symfony.com/doc/current/components/http_foundation/introduction.html#streaming-a-response
     *
     *
     *
     *
     *
     *
     * Należy dokładnie zwalidować ścieżkę $file którą tutaj przekazujemy
     * Niedbale skonstruowany download może umożliwiać użytkownikowi zewnętrznemu ściąganie wrażliwych danych z serwera.
     *
     * @param string $file
     * @param string $filename
     *
     * @return Response
     */
    public static function downloadDie($file, $filename = null)
    {
        if (!$filename) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
        }

        $size = filesize($file);

        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . urlencode($file));
        header("Content-Description: File Transfer ".urlencode($file));
        header('Last-Modified: '.date('D, d M Y H:i:s \G\M\T'));
        header("Content-Length: $size");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        readfile($file);

        return new Response();
    }
    /**
     * Man http://symfony.com/doc/current/components/http_foundation/introduction.html#serving-files
     * g(When sending a file, you must add a Content-Disposition header to your response symfony2)
     * @param string $file
     * @param string $filename
     */
    public static function downloadStatic($file, $headers = array()) {

        if (!is_array($headers)) {
            $headers = array();
        }

        $headers = array_merge(array(
            'Content-Type' => 'application/octet-stream'
        ), $headers);

        $respone = new BinaryFileResponse(
            $file,
            $status = 200,
            $headers,
            $public = true,
            $contentDisposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $autoEtag = true,
            $autoLastModified = true
        );

        return $respone;
    }
    /**
     * Aby powstrzymać przed downloadem, tak aby otwarło w przeglądarce:
        return DownloadFile::downloadGenerated($pdfContent, $filename, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => false
        ));

     * @param type $data
     * @param type $filename
     * @param type $headers
     * @return type
     */
    public static function downloadGenerated($data, $filename, $headers = array()) {

        $response = new Response();

        if (!is_array($headers)) {
            $headers = array();
        }

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            pathinfo($filename, PATHINFO_BASENAME)
        );

        $headers = array_merge(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => $disposition
        ), $headers);

//niechginie($headers);

        foreach ($headers as $key => &$value) {
            $response->headers->set($key, $value);
        }

        return $response->setCache(array(
            'last_modified' => new DateTime(),
            'max_age'       => 0,
            's_maxage'      => 0,
            'private'       => false,
            'public'        => true,
        ))->setContent($data);
    }
}
