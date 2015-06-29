<?php

namespace Stopsopa\UtilsBundle\Lib\FileProcessors;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\FileProcessors\Tools\AbstractFileProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Form;
use Stopsopa\UtilsBundle\Lib\FileProcessors\Tools\UploadResult;
use Exception;
use Stopsopa\UtilsBundle\Lib\Standalone\Urlizer;

class CommentFileProcessor extends AbstractFileProcessor {
    public function handle(UploadedFile $file, UploadResult $result) {

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
    public function getConfig() {
        return array(
            'web'    => AbstractApp::getRootDir().'/web',
            'dirtmp' => '/media/uploads/comments_tmp',
            'dir'    => '/media/uploads/comments',
            'file'   => '#^user\.comments\.\d+\.file#',
            'field'  => 'path',
        );
    }
}

