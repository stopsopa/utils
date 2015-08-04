<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\HttpFoundation\Response;

class DownloadFile
{
    /**
     * Należy dokładnie zwalidować ścieżkę $file którą tutaj przekazujemy
     * Niedbale skonstruowany download może umożliwiać użytkownikowi zewnętrznemu ściąganie wrażliwych danych z serwera.
     *
     * @param string $file
     * @param string $filename
     *
     * @return Response
     */
    public static function download($file, $filename = null)
    {
        if (!$filename) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
        }

        $size = filesize($file);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Last-Modified: '.date('D, d M Y H:i:s \G\M\T'));
        header("Content-Length: $size");

        readfile($file);

        return new Response();
    }
}
