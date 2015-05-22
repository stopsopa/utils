<?php

namespace Stopsopa\UtilsBundle\Lib;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\HttpFoundation\Response as Base;
use Stopsopa\UtilsBundle\Lib\Json\Conditionally\Json;

/**
 * Description of Response.
 *
 * @author Jacek
 */
class Response extends Base
{
    /**
     * @author Szymon Działowski
     *
     * @var array
     */
    protected $json;

    public function setMimeType($arg)
    {
        if (strpos($arg, '|')) {
            $arg = reset(explode('|', $arg));
        }

        $this->headers->set('content-type', $arg);

        return $this;
    }

    const MIME_DIR      = 'directory';
    const MIME_AI        = 'application/postscript';
    const MIME_EPS    = 'application/postscript';
    const MIME_EXE    = 'application/x-executable';
    const MIME_DOC    = 'application/vnd.ms-word';
    const MIME_XLS    = 'application/vnd.ms-excel';
    const MIME_PPT    = 'application/vnd.ms-powerpoint';
    const MIME_PPS    = 'application/vnd.ms-powerpoint';
    const MIME_PDF    = 'application/pdf';
    const MIME_XML    = 'application/xml';
    const MIME_SWF    = 'application/x-shockwave-flash';
    const MIME_TORRENT    = 'application/x-bittorrent';
    const MIME_JAR    = 'application/x-jar';
    const MIME_ODT    = 'application/vnd.oasis.opendocument.text';
    const MIME_OTT    = 'application/vnd.oasis.opendocument.text-template';
    const MIME_OTH    = 'application/vnd.oasis.opendocument.text-web';
    const MIME_ODM    = 'application/vnd.oasis.opendocument.text-master';
    const MIME_ODG    = 'application/vnd.oasis.opendocument.graphics';
    const MIME_OTG    = 'application/vnd.oasis.opendocument.graphics-template';
    const MIME_ODP    = 'application/vnd.oasis.opendocument.presentation';
    const MIME_OTP    = 'application/vnd.oasis.opendocument.presentation-template';
    const MIME_ODS    = 'application/vnd.oasis.opendocument.spreadsheet';
    const MIME_OTS    = 'application/vnd.oasis.opendocument.spreadsheet-template';
    const MIME_ODC    = 'application/vnd.oasis.opendocument.chart';
    const MIME_ODF    = 'application/vnd.oasis.opendocument.formula';
    const MIME_ODB    = 'application/vnd.oasis.opendocument.database';
    const MIME_ODI    = 'application/vnd.oasis.opendocument.image';
    const MIME_OXT    = 'application/vnd.openofficeorg.extension';
    const MIME_DOCX    = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const MIME_DOCM    = 'application/vnd.ms-word.document.macroEnabled.12';
    const MIME_DOTX    = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    const MIME_DOTM    = 'application/vnd.ms-word.template.macroEnabled.12';
    const MIME_XLSX    = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const MIME_XLSM    = 'application/vnd.ms-excel.sheet.macroEnabled.12';
    const MIME_XLTX    = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    const MIME_XLTM    = 'application/vnd.ms-excel.template.macroEnabled.12';
    const MIME_XLSB    = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
    const MIME_XLAM    = 'application/vnd.ms-excel.addin.macroEnabled.12';
    const MIME_PPTX    = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    const MIME_PPTM    = 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
    const MIME_PPSX    = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    const MIME_PPSM    = 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
    const MIME_POTX    = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    const MIME_POTM    = 'application/vnd.ms-powerpoint.template.macroEnabled.12';
    const MIME_PPAM    = 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
    const MIME_SLDX    = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
    const MIME_SLDM    = 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
    const MIME_GZ        = 'application/x-gzip';
    const MIME_TGZ    = 'application/x-gzip';
    const MIME_BZ        = 'application/x-bzip2';
    const MIME_BZ2    = 'application/x-bzip2';
    const MIME_TBZ    = 'application/x-bzip2';
    const MIME_ZIP    = 'application/zip';
    const MIME_RAR    = 'application/x-rar';
    const MIME_TAR    = 'application/x-tar';
    const MIME_7Z        = 'application/x-7z-compressed';
    const MIME_GPX        = 'application/gpx';
    const MIME_TXT    = 'text/plain';
    const MIME_PHP    = 'text/x-php';
    const MIME_HTML    = 'text/html';
    const MIME_HTM    = 'text/html';
    const MIME_JS        = 'text/javascript';
    const MIME_CSS    = 'text/css';
    const MIME_RTF    = 'text/rtf';
    const MIME_RTFD    = 'text/rtfd';
    const MIME_PY        = 'text/x-python';
    const MIME_JAVA    = 'text/x-java-source';
    const MIME_RB        = 'text/x-ruby';
    const MIME_SH        = 'text/x-shellscript';
    const MIME_PL        = 'text/x-perl';
    const MIME_SQL    = 'text/x-sql';
    const MIME_C         = 'text/x-csrc';
    const MIME_H         = 'text/x-chdr';
    const MIME_CPP    = 'text/x-c++src';
    const MIME_HH        = 'text/x-c++hdr';
    const MIME_LOG    = 'text/plain';
    const MIME_CSV    = 'text/x-comma-separated-values';
    const MIME_BMP    = 'image/x-ms-bmp';
    const MIME_JPG    = 'image/jpeg';
    const MIME_JPEG    = 'image/jpeg';
    const MIME_GIF    = 'image/gif';
    const MIME_PNG    = 'image/png';
    const MIME_TIF    = 'image/tiff';
    const MIME_TIFF    = 'image/tiff';
    const MIME_TGA    = 'image/x-targa';
    const MIME_PSD    = 'image/vnd.adobe.photoshop';
    const MIME_XBM    = 'image/xbm';
    const MIME_PXM    = 'image/pxm';
    const MIME_MP3    = 'audio/mpeg';
    const MIME_MID    = 'audio/midi';
    const MIME_OGG    = 'audio/ogg';
    const MIME_OGA    = 'audio/ogg';
    const MIME_M4A    = 'audio/mp4';
    const MIME_WAV    = 'audio/wav';
    const MIME_WMA    = 'audio/x-ms-wma';
    const MIME_AAC     = 'audio/aac';
    const MIME_AVI    = 'video/x-msvideo';
    const MIME_DV        = 'video/x-dv';
    const MIME_MP4    = 'video/mp4';
    const MIME_MPEG    = 'video/mpeg';
    const MIME_MPG    = 'video/mpeg';
    const MIME_MOV    = 'video/quicktime';
    const MIME_WM        = 'video/x-ms-wmv';
    const MIME_FLV    = 'video/x-flv';
    const MIME_MKV    = 'video/x-matroska';
    const MIME_WEBM    = 'video/webm';
    const MIME_OGV    = 'video/ogg';
    const MIME_OGM    = 'video/og';

    /**
     * @param type $ext
     *
     * @return type
     */
    public static function getMimeFromExtension($ext)
    {
        return constant(get_class().'::MIME_'.strtoupper($ext));
    }
    /**
     * @author Szymon Działowski
     *
     * @return array
     */
    public function getJson()
    {
        return is_array($this->json) ? $this->json : array();
    }
    public function setContent($content, $setjsonheader = false)
    {
        if (is_array($content)) {
            $this->json = $content;
            $setjsonheader and $this->headers->set('content-type', 'application/json; charset=utf-8');

            return $this->setContent(Json::encode($content));
        }

        return parent::setContent($content);
    }

    /**
     * @param array $array
     *
     * @return Response
     */
    public function extendJson(array $array = array(), $setjsonheader = false)
    {
        $json = $this->getJson();

        return $this->setContent(UtilArray::arrayMergeRecursiveDistinct($json, $array), $setjsonheader);
    }
}
