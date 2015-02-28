<?php

namespace Stopsopa\UtilsBundle\Composer;
use Composer\Script\Event;
use InputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Stopsopa\UtilsBundle\Init
 */
class InstallerPart extends AbstractInstallerPart {
    public static function install(InputInterface $input, OutputInterface $output) {
        file_put_contents('log.log.log', $event->getName()."\n", FILE_APPEND);
    }
}    