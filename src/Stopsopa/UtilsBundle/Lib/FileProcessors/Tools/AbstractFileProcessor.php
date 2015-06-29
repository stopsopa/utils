<?php

namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Exception;
use Stopsopa\UtilsBundle\Lib\Standalone\Urlizer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFileProcessor {
    /**
     * Tutaj należy przeprowadzić walidację i jeśli walidacja się powiedzie to przenieść do katalogu tymczasowego
     * katalog tymczasowy powinien być w przestrzeni katalogowej widocznej z web
     * na koniec zwracamy dane dotyczące nowego pliki, najważniejsze w tych danych jest aby podać ścieżkę
     * od web do pliku, tak aby na stronie można było załadować
     * po drodze należy także ustawić path do przekazywania w polu hidden
     * @param UploadedFile $file
     * @param Form $form
     * @param UploadResult $result
     * @throws Exception
     */
    public function handle(UploadedFile $file, UploadResult $result) {
        throw new Exception("Method not implemented");

        // validate and if error
        $result->addError('File too big');

        // if ok move file and pass new path

        $config = $this->getConfig();

        $newfilename = Urlizer::urlizeCaseSensitiveTrim(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = $file->guessExtension();

        if (!$ext) {
            $ext = 'bin';
        }
        $newfilename .= '.' . $ext;

        $directory = $this->generateSafeDirPrefix($newfilename);

        $file->move($config['web'].$config['dirtmp'].$directory, $newfilename);

        $result->setResponse(array(
            'web' => $config['dir'].$newfilename
        ));

        $result->setPath($directory.'/'.$newfilename);
    }
    public static function getConfig() {
        throw new Exception("Method not implemented");

//        return array(
//            'class'  => 'Namespace/classname',
//            'web'    => $root.'/web',
//            'dirtmp' => '/media/uploads/users_tmp',
//            'dir'    => '/media/uploads/users',
//            'file'   => '#^user\.comments\.\d+\.file#',
//            'field'  => 'path',
//        );
    }
    public function delete($entity) {
        throw new Exception("Method not implemented");
        /* @var $entity User */
//        if ($path = $entity->getWebPath()) {
//            $config = $this->getConfig();
//
//            $tmp = $config['web'].$config['dirtmp'].$path;
//            if (file_exists($tmp)) {
//                UtilFilesystem::removeFile($tmp);
//                UtilFilesystem::removeEmptyDirsToPath(dirname($tmp), $config['web'].$config['dirtmp']);
//            }
//
//            $tmp = $config['web'].$config['dir'].$path;
//            if (file_exists($tmp)) {
//                UtilFilesystem::removeFile($tmp);
//                UtilFilesystem::removeEmptyDirsToPath(dirname($tmp), $config['web'].$config['dir']);
//            }
//        }
    }
    protected function generateSafeDirPrefix($filename) {

        $config = $this->getConfig();

        do {
            $dir = '/'.substr(md5(uniqid(mt_rand(), true)), 0, 5);

            $dir[3] = '/';

            $absolutedir = $config['web'].$config['dir'].$dir;

            $tmpabsolutedir = $config['web'].$config['dirtmp'].$dir;

        } while (file_exists($absolutedir . '/' . $filename) || file_exists($tmpabsolutedir . '/' . $filename));

        return $dir;
    }
    public static function _getConfig($key = null) {
        if ($key) {
            $tmp = static::getConfig();
            if ($key == 'fieldmatch') {
                return preg_replace('#^(.*[\.\[\]])[^\.\[\]\#\/]+([\#\/])$#', '$1'.$tmp['field'].'$2', $tmp['file']);
            }
            if (array_key_exists($key, $tmp)) {
                return $tmp[$key];
            }
        }
        throw new Exception("Key '".$key."' not found in config: ".  json_encode($tmp));
    }
}

