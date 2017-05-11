<?php

namespace Stopsopa\UtilsBundle\Composer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Stopsopa\UtilsBundle\Init.
 */
class InstallerPart extends AbstractInstallerPart
{
    public function install(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('InstallerPart');
    }
}
