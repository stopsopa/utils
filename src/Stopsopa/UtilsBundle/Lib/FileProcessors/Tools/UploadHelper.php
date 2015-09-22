<?php

namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use DateTime;

class UploadHelper
{
    /**
     * @var BaseRequest
     */
    protected $request;
    /**
     * @var AbstractFileProcessor[]
     */
    protected $processors;

    protected $response;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
        $this->processors = array();
    }
    public function addProcessor(AbstractFileProcessor $processor)
    {
        $this->processors[] = $processor;
    }
    public function countFiles()
    {
        $paths = $this->_extractFilePaths($this->request->files->all());
//        nieginie($this->request->files->all(), 2);
//        nieginie(count($paths));
//        niechginie($paths, 2);
        return count($paths);
    }

    /**
     * $d = UtilFormAccessor::setValue($form, 'user[comments][1][path]', 'test');.
     */
    public function handle()
    {
        $paths = $this->_extractFilePaths($this->request->files->all());

        $response = array();

        foreach ($paths as $path => &$file) {
            $processor = $this->_getProcessor($path);
            $config = $processor->getConfig();

            $prefix = preg_replace('#^(.*?)\.[^\.]+$#', '$1', $path);

//            if (strpos($prefix, '.') === false) {
//                $form = $this->form;
//            }
//            else {

//                $form = UtilFormAccessor::getForm($this->form, $prefix);
//
//                niechginie(array(
//                    $prefix,
//                    $this->form,
//                    $form,
//                    $form->getData()
//                ));
//
////                niechginie($form);
//            }

            $result = new UploadResult($prefix.'.'.$config['field'], str_replace('.', '_', $path), $this->request);

            $response[] = $result;

            $processor->handle($file, $result);
        }

        $return = array();
        foreach ($response as $res) {
            /* @var $res UploadResult */
            $return[] = $res->getResult();
        }

        return array(
            'files' => $return,
        );
    }
    public function move()
    {
        $paths = $this->_extractHiddenPaths($this->request->request->all());

        foreach ($paths as $path => $file) {
            if (trim($file)) {
                $processor = $this->_getProcessorByPath($path);

                $config = $processor->getConfig();

                $tmp = $config['web'].$config['dirtmp'].$file;

                if (file_exists($tmp)) {
                    UtilFilesystem::rename($tmp, $config['web'].$config['dir'].$file);
                    UtilFilesystem::removeEmptyDirsToPath(dirname($tmp), $config['web'].$config['dirtmp']);
                }
            }
        }
    }

    public function getData()
    {
        $list = array();

        foreach ($this->response as $response) {
            /* @var $response UploadResult */
            $list[] = $response->getPath();
        }

        return $list;
    }

    protected function _extractFilePaths($paths)
    {
        $list = array();

        $this->_extractOneFile($paths, '', $list);

        return $list;
    }

    protected function _extractOneFile(&$tree, $prefix, &$list)
    {
        foreach ($tree as $key => &$data) {
            if ($prefix) {
                $key = $prefix.'.'.$key;
            }

            if ($data instanceof UploadedFile) {
                $list[$key] = $data;
                continue;
            }

            if (is_array($data)) {
                $this->_extractOneFile($data, $key, $list);
            }
        }
    }

    protected function _extractHiddenPaths($paths)
    {
        $list = array();

        $this->_extractOneHiddenPath($paths, '', $list);

        return $list;
    }
    protected function _extractOneHiddenPath(&$tree, $prefix, &$list)
    {
        foreach ($tree as $key => &$data) {
            if ($prefix) {
                $tmpkey = $prefix.'.'.$key;
            } else {
                $tmpkey = $key;
            }

            $processor = $this->_getProcessorByPath($tmpkey);

            if ($processor && $key === $processor->_getConfig('field')) {
                $list[$tmpkey] = $data;
                continue;
            }

            if (is_array($data)) {
                $this->_extractOneHiddenPath($data, $tmpkey, $list);
            }
        }
    }
    public static $processorcache;
    /**
     * @param string $path
     *
     * @return AbstractFileProcessor
     */
    protected function _getProcessor($path)
    {
        if (!$path) {
            return;
        }

        if (!is_array(static::$processorcache)) {
            static::$processorcache = array();
        }

        if (array_key_exists($path, static::$processorcache)) {
            return static::$processorcache[$path];
        }

        foreach ($this->processors as &$processor) {

            /* @var $processor AbstractFileProcessor */
            $config = $processor->getConfig();

            if (preg_match($config['file'], $path)) {
                static::$processorcache[$path] = $processor;

                return $processor;
            }
        }
    }
    public static $processorcachepath;
    /**
     * @param string $path
     *
     * @return AbstractFileProcessor
     */
    protected function _getProcessorByPath($path)
    {
        if (!$path) {
            return;
        }

        if (!is_array(static::$processorcachepath)) {
            static::$processorcachepath = array();
        }

        if (array_key_exists($path, static::$processorcachepath)) {
            return static::$processorcachepath[$path];
        }

        foreach ($this->processors as &$processor) {

            /* @var $processor AbstractFileProcessor */
            $match = $processor->_getConfig('fieldmatch');

            if (preg_match($match, $path)) {
                static::$processorcachepath[$path] = $processor;

                return $processor;
            }
        }
    }
    /**
     * // $_SERVER[HTTP_IF_MODIFIED_SINCE] => Tue, 04 Aug 2015 15:34:00 GMT
     * aby zmienić czas modyfikacji pliku wystarczy echo 'console.log('go');' > test.js && date
     *
     * po stronie php:

    $modified = HttpIfModifiedSince::isModified($file, @$_SERVER['HTTP_IF_MODIFIED_SINCE']);

    if ($modified) {
    // http://php.net/manual/en/class.datetime.php
    header('Last-Modified: '.  gmdate("D, d M Y H:i:s T", filemtime($file))  ); // tak lepiej Last-Modified: Tue, 22 Sep 2015 21:15:31 GMT
    //        header('Last-Modified: '.  date("D, d M Y H:i:s T", filemtime($file))  ); //            Last-Modified: Tue, 22 Sep 2015 23:16:05 CEST
    readfile($file);
    }
    else {
    header('HTTP/1.0 304 Not Modified');
    }
    die();
     *
     * po stronie js:
     *
    "Tue, 22 Sep 2015 20:15:36 GMT"   http://stackoverflow.com/a/13594069/1338731    new - RFC 1123, and old RFC 822
    (new Date()).toUTCString()
    var t = new Date ()
    console.log(t.toUTCString())
    d1.setMinutes ( t.getMinutes() + 30 );
    console.log(t.toUTCString())
     * @param $file
     * @param null $lastmodifiedheader
     * @return bool
     * @throws Exception
     *
     * test: PD9waHANCi8vdXNlIFN5bWZvbnlcQ29tcG9uZW50XEh0dHBGb3VuZGF0aW9uXFJlcXVlc3Q7DQp1c2UgU3RvcHNvcGFcVXRpbHNCdW5kbGVcTGliXFJlcXVlc3Q7DQp1c2UgU3ltZm9ueVxDb21wb25lbnRcRGVidWdcRGVidWc7DQpyZXF1aXJlX29uY2UgX19ESVJfXy4nLy4uL2FwcC9Db21tb25Ub29scy5waHAnOw0KDQoNCmlmIChpc3NldCgkX0dFVFsnY2FjaGUnXSkpIHsNCg0KICAgICRmaWxlID0gZGlybmFtZShfX0ZJTEVfXykuJy8nLiRfR0VUWydjYWNoZSddOw0KDQoNCg0KICAgIGNsYXNzIEh0dHBJZk1vZGlmaWVkU2luY2Ugew0KICAgICAgICAvKioNCiAgICAgICAgICogLy8gJF9TRVJWRVJbSFRUUF9JRl9NT0RJRklFRF9TSU5DRV0gPT4gVHVlLCAwNCBBdWcgMjAxNSAxNTozNDowMCBHTVQNCiAgICAgICAgICogYWJ5IHptaWVuacSHIGN6YXMgbW9keWZpa2FjamkgcGxpa3Ugd3lzdGFyY3p5IGVjaG8gJ2NvbnNvbGUubG9nKCdnbycpOycgPiB0ZXN0LmpzICYmIGRhdGUNCiAgICAgICAgICoNCiAgICAgICAgICogcG8gc3Ryb25pZSBwaHA6DQoNCiAgICAgICAgICAgICRtb2RpZmllZCA9IEh0dHBJZk1vZGlmaWVkU2luY2U6OmlzTW9kaWZpZWQoJGZpbGUsIEAkX1NFUlZFUlsnSFRUUF9JRl9NT0RJRklFRF9TSU5DRSddKTsNCg0KICAgICAgICAgICAgaWYgKCRtb2RpZmllZCkgew0KICAgICAgICAgICAgLy8gaHR0cDovL3BocC5uZXQvbWFudWFsL2VuL2NsYXNzLmRhdGV0aW1lLnBocA0KICAgICAgICAgICAgaGVhZGVyKCdMYXN0LU1vZGlmaWVkOiAnLiAgZ21kYXRlKCJELCBkIE0gWSBIOmk6cyBUIiwgZmlsZW10aW1lKCRmaWxlKSkgICk7IC8vIHRhayBsZXBpZWogTGFzdC1Nb2RpZmllZDogVHVlLCAyMiBTZXAgMjAxNSAyMToxNTozMSBHTVQNCiAgICAgICAgICAgIC8vICAgICAgICBoZWFkZXIoJ0xhc3QtTW9kaWZpZWQ6ICcuICBkYXRlKCJELCBkIE0gWSBIOmk6cyBUIiwgZmlsZW10aW1lKCRmaWxlKSkgICk7IC8vICAgICAgICAgICAgTGFzdC1Nb2RpZmllZDogVHVlLCAyMiBTZXAgMjAxNSAyMzoxNjowNSBDRVNUDQogICAgICAgICAgICByZWFkZmlsZSgkZmlsZSk7DQogICAgICAgICAgICB9DQogICAgICAgICAgICBlbHNlIHsNCiAgICAgICAgICAgIGhlYWRlcignSFRUUC8xLjAgMzA0IE5vdCBNb2RpZmllZCcpOw0KICAgICAgICAgICAgfQ0KICAgICAgICAgICAgZGllKCk7DQogICAgICAgICAqDQogICAgICAgICAqDQogICAgICAgICAqIHBvIHN0cm9uaWUganM6DQogICAgICAgICAqDQogICAgICAgICAgICAiVHVlLCAyMiBTZXAgMjAxNSAyMDoxNTozNiBHTVQiICAgaHR0cDovL3N0YWNrb3ZlcmZsb3cuY29tL2EvMTM1OTQwNjkvMTMzODczMSAgICBuZXcgLSBSRkMgMTEyMywgYW5kIG9sZCBSRkMgODIyDQogICAgICAgICAgICAobmV3IERhdGUoKSkudG9VVENTdHJpbmcoKQ0KICAgICAgICAgICAgdmFyIHQgPSBuZXcgRGF0ZSAoKQ0KICAgICAgICAgICAgY29uc29sZS5sb2codC50b1VUQ1N0cmluZygpKQ0KICAgICAgICAgICAgZDEuc2V0TWludXRlcyAoIHQuZ2V0TWludXRlcygpICsgMzAgKTsNCiAgICAgICAgICAgIGNvbnNvbGUubG9nKHQudG9VVENTdHJpbmcoKSkNCiAgICAgICAgICogQHBhcmFtICRmaWxlDQogICAgICAgICAqIEBwYXJhbSBudWxsICRsYXN0bW9kaWZpZWRoZWFkZXINCiAgICAgICAgICogQHJldHVybiBib29sDQogICAgICAgICAqIEB0aHJvd3MgRXhjZXB0aW9uDQogICAgICAgICAqLw0KICAgICAgICBwdWJsaWMgc3RhdGljIGZ1bmN0aW9uIGlzTW9kaWZpZWQoJGZpbGUsICRsYXN0bW9kaWZpZWRoZWFkZXIgPSBudWxsKQ0KICAgICAgICB7DQogICAgICAgICAgICBpZiAoISRsYXN0bW9kaWZpZWRoZWFkZXIpIHsNCiAgICAgICAgICAgICAgICByZXR1cm4gdHJ1ZTsNCiAgICAgICAgICAgIH0NCg0KICAgICAgICAgICAgJGRhdGUgICA9IG5ldyBEYXRlVGltZShkYXRlKCJjIiwgc3RydG90aW1lKCRsYXN0bW9kaWZpZWRoZWFkZXIpKSk7DQoNCiAgICAgICAgICAgICRzdGFydCAgPSBuZXcgRGF0ZVRpbWUoJzE5NzAtMDEtMDEnKTsNCg0KICAgICAgICAgICAgaWYgKCRkYXRlID4gJHN0YXJ0KSB7IC8vIGplc3Qgc2Vuc293bmEgZGF0YSBhIG5pZSAxOTcwLTAxLTAxDQoNCiAgICAgICAgICAgICAgICBpZiAoIWZpbGVfZXhpc3RzKCRmaWxlKSkgew0KICAgICAgICAgICAgICAgICAgICB0aHJvdyBuZXcgRXhjZXB0aW9uKCJGaWxlICckZmlsZScgbm90IGV4aXN0cyIpOw0KICAgICAgICAgICAgICAgIH0NCg0KICAgICAgICAgICAgICAgIGlmICghaXNfZmlsZSgkZmlsZSkpIHsNCiAgICAgICAgICAgICAgICAgICAgdGhyb3cgbmV3IEV4Y2VwdGlvbigiJyRmaWxlJyBpcyBub3QgZmlsZSIpOw0KICAgICAgICAgICAgICAgIH0NCg0KICAgICAgICAgICAgICAgIGlmICghaXNfcmVhZGFibGUoJGZpbGUpKSB7DQogICAgICAgICAgICAgICAgICAgIHRocm93IG5ldyBFeGNlcHRpb24oIkZpbGUgJyRmaWxlJyBub3QgcmVhZGFibGUiKTsNCiAgICAgICAgICAgICAgICB9DQoNCiAgICAgICAgICAgICAgICAkbW9kICAgID0gbmV3IERhdGVUaW1lKGRhdGUoImMiLCBmaWxlbXRpbWUoJGZpbGUpKSk7DQoNCi8vICAgICAgICAgICAgICAgIG5pZWNoZ2luaWUoYXJyYXkoIC8vIGRlYnVnDQovLyAgICAgICAgICAgICAgICAgICAgJyRkYXRlJyA9PiAkZGF0ZS0+Zm9ybWF0KCdZLW0tZCBIOmk6cycpLA0KLy8gICAgICAgICAgICAgICAgICAgICckbW9kJyA9PiAkbW9kLT5mb3JtYXQoJ1ktbS1kIEg6aTpzJyksDQovLyAgICAgICAgICAgICAgICAgICAgJyRtb2QgPiAkZGF0ZScgPT4gJG1vZCA+ICRkYXRlDQovLyAgICAgICAgICAgICAgICApKTsNCg0KICAgICAgICAgICAgICAgIGlmICgkbW9kID4gJGRhdGUpIHsNCiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIHRydWU7DQogICAgICAgICAgICAgICAgfQ0KDQogICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlOw0KICAgICAgICAgICAgfQ0KDQogICAgICAgICAgICB0aHJvdyBuZXcgRXhjZXB0aW9uKCdXcm9uZyBJZi1Nb2RpZmllZC1TaW5jZSBoZWFkZXIgKCcuJGxhc3Rtb2RpZmllZGhlYWRlci4nKSwgc2hvdWxkIGJlIGV4YW1wbGU6IElmLU1vZGlmaWVkLVNpbmNlOiBUdWUsIDIyIFNlcCAyMDE1IDIwOjI2OjAzIEdNVCcpOw0KICAgICAgICB9DQogICAgfQ0KDQogICAgJG1vZGlmaWVkID0gSHR0cElmTW9kaWZpZWRTaW5jZTo6aXNNb2RpZmllZCgkZmlsZSwgQCRfU0VSVkVSWydIVFRQX0lGX01PRElGSUVEX1NJTkNFJ10pOw0KDQogICAgaWYgKCRtb2RpZmllZCkgew0KICAgICAgICAvLyBodHRwOi8vcGhwLm5ldC9tYW51YWwvZW4vY2xhc3MuZGF0ZXRpbWUucGhwDQogICAgICAgIGhlYWRlcignTGFzdC1Nb2RpZmllZDogJy4gIGdtZGF0ZSgiRCwgZCBNIFkgSDppOnMgVCIsIGZpbGVtdGltZSgkZmlsZSkpICApOyAvLyB0YWsgbGVwaWVqIExhc3QtTW9kaWZpZWQ6IFR1ZSwgMjIgU2VwIDIwMTUgMjE6MTU6MzEgR01UDQovLyAgICAgICAgaGVhZGVyKCdMYXN0LU1vZGlmaWVkOiAnLiAgZGF0ZSgiRCwgZCBNIFkgSDppOnMgVCIsIGZpbGVtdGltZSgkZmlsZSkpICApOyAvLyAgICAgICAgICAgIExhc3QtTW9kaWZpZWQ6IFR1ZSwgMjIgU2VwIDIwMTUgMjM6MTY6MDUgQ0VTVA0KICAgICAgICByZWFkZmlsZSgkZmlsZSk7DQogICAgfQ0KICAgIGVsc2Ugew0KICAgICAgICBoZWFkZXIoJ0hUVFAvMS4wIDMwNCBOb3QgTW9kaWZpZWQnKTsNCiAgICB9DQogICAgZGllKCk7DQoNCg0KDQogICAgSGVhZGVyKCJIVFRQLzEuMSAzMDQgTm90IE1vZGlmaWVkIik7DQoNCi8vICAgIGRpZSgna29uaWVjJyk7DQogICAgZXJyb3JfcmVwb3J0aW5nKEVfQUxMKTsNCiAgICBpbmlfc2V0KCdkaXNwbGF5X2Vycm9ycycsMSk7DQoNCiAgICBoZWFkZXIoJ0NvbnRlbnQtVHlwZTogYXBwbGljYXRpb24vamF2YXNjcmlwdCcpOw0KICAgIGhlYWRlcignQWNjZXNzLUNvbnRyb2wtQWxsb3ctT3JpZ2luOiAqJyk7DQogICAgaGVhZGVyKCdDRi1DYWNoZS1TdGF0dXM6IEhJVCcpOw0KICAgIGhlYWRlcignQ0YtUkFZOiAyMmEwMmU1M2FmN2YyNjVhLUZSQScpOw0KICAgIGhlYWRlcignQ2FjaGUtQ29udHJvbDogcHVibGljLCBtYXgtYWdlPTMwNjcyMDAwJyk7DQogICAgaGVhZGVyKCdDb25uZWN0aW9uOiBrZWVwLWFsaXZlJyk7DQogICAgaGVhZGVyKCdEYXRlOiBUdWUsIDIyIFNlcCAyMDE1IDE4OjQ3OjU0IEdNVCcpOw0KICAgIGhlYWRlcignRXhwaXJlczogU3VuLCAxMSBTZXAgMjAxNiAxODo0Nzo1NCBHTVQnKTsNCiAgICBoZWFkZXIoJ0xhc3QtTW9kaWZpZWQ6IFR1ZSwgMDQgQXVnIDIwMTUgMTU6MzQ6MDAgR01UJyk7DQogICAgaGVhZGVyKCdTZXJ2ZXI6IGNsb3VkZmxhcmUtbmdpbngnKTsNCiAgICBoZWFkZXIoJ1Zhcnk6IEFjY2VwdC1FbmNvZGluZycpOw0KDQogICAgcmVhZGZpbGUoZGlybmFtZShfX0ZJTEVfXykuJy8nLiRfR0VUWydjYWNoZSddKTsNCg0KICAgIGRpZSgna29uaWVjJyk7DQp9DQo/Pg0KPCFkb2N0eXBlIGh0bWw+DQo8aHRtbCBsYW5nPSJlbiI+DQo8aGVhZD4NCiAgICA8bWV0YSBjaGFyc2V0PSJVVEYtOCI+DQogICAgPHRpdGxlPkRvY3VtZW50PC90aXRsZT4NCiAgICA8c2NyaXB0IHNyYz0idGVzdC5qcyI+PC9zY3JpcHQ+DQogICAgPHNjcmlwdCBzcmM9Imh0dHBzOi8vY2RuanMuY2xvdWRmbGFyZS5jb20vYWpheC9saWJzL2pxdWVyeS8zLjAuMC1hbHBoYTEvanF1ZXJ5Lm1pbi5qcyI+PC9zY3JpcHQ+DQogICAgPHNjcmlwdCBzcmM9Ij9jYWNoZT10ZXN0LmpzIj48L3NjcmlwdD4NCiAgICA8c2NyaXB0Pg0KICAgICAgICAkKGZ1bmN0aW9uICgpIHsNCiAgICAgICAgICAgICQoJy50JykuY2xpY2soZnVuY3Rpb24gKCkgew0KICAgICAgICAgICAgICAgICQuYWpheCgnP2NhY2hlPXRlc3QuanMnKTsNCiAgICAgICAgICAgIH0pOw0KDQogICAgICAgICAgICAkKCcuaCcpLmNsaWNrKGZ1bmN0aW9uICgpIHsNCiAgICAgICAgICAgICAgICB2YXIgdCA9IG5ldyBEYXRlICgpDQogICAgICAgICAgICAgICAgdC5zZXRTZWNvbmRzICggdC5nZXRTZWNvbmRzKCkgLSAxMCApOw0KDQogICAgICAgICAgICAgICAgJC5hamF4KCc/Y2FjaGU9dGVzdC5qcycsIHsNCi8vICAgICAgICAgICAgICAgICAgICBpZk1vZGlmaWVkOiB0cnVlIC8vIGRvbmUgdHlsa28gamXFm2xpIG1vZGlmaWVkLCBuaWUgcG9kbm9zaSBkb25lIGplxZtsaSAzMDQgLSBuaWUgc3ByYXdkemHFgmVtIGN6eSB0YWsgamVzdCwgbWFudWFsIHRhayBtw7N3aQ0KICAgICAgICAgICAgICAgICAgICBoZWFkZXJzOiB7DQogICAgICAgICAgICAgICAgICAgICAgICAnSWYtTW9kaWZpZWQtU2luY2UnOiB0LnRvVVRDU3RyaW5nKCkNCiAgICAgICAgICAgICAgICAgICAgfQ0KICAgICAgICAgICAgICAgIH0pDQogICAgICAgICAgICAgICAgLmRvbmUoZnVuY3Rpb24gKCkgew0KICAgICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKCdtb2RpZmllZCcpDQogICAgICAgICAgICAgICAgfSk7DQogICAgICAgICAgICB9KTsNCiAgICAgICAgfSkNCiAgICA8L3NjcmlwdD4NCjwvaGVhZD4NCjxib2R5Pg0KPGJ1dHRvbiBjbGFzcz0idCI+dGVzdDwvYnV0dG9uPg0KPGJ1dHRvbiBjbGFzcz0iaCI+aGVhZGVyPC9idXR0b24+DQo8L2JvZHk+DQo8L2h0bWw+
     */
    public static function isModified($file, $lastmodifiedheader = null)
    {
        if (!$lastmodifiedheader) {
            return true;
        }

        $date   = new DateTime(date("c", strtotime($lastmodifiedheader)));

        $start  = new DateTime('1970-01-01');

        if ($date > $start) { // jest sensowna data a nie 1970-01-01

            if (!file_exists($file)) {
                throw new Exception("File '$file' not exists");
            }

            if (!is_file($file)) {
                throw new Exception("'$file' is not file");
            }

            if (!is_readable($file)) {
                throw new Exception("File '$file' not readable");
            }

            $mod    = new DateTime(date("c", filemtime($file)));

//                niechginie(array( // debug
//                    '$date' => $date->format('Y-m-d H:i:s'),
//                    '$mod' => $mod->format('Y-m-d H:i:s'),
//                    '$mod > $date' => $mod > $date
//                ));

            if ($mod > $date) {
                return true;
            }

            return false;
        }

        throw new Exception('Wrong If-Modified-Since header ('.$lastmodifiedheader.'), should be example: If-Modified-Since: Tue, 22 Sep 2015 20:26:03 GMT');
    }
    public function delete($entity)
    {
        foreach ($this->processors as $processor) {
            $config = $processor->getConfig();

            if ($entity instanceof $config['class']) {
                return $processor->delete($entity);
            }
        }
    }
}
