<?php

namespace Stopsopa\UtilsBundle\Composer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractInstallerPart
{
    /**
     * Wyższa liczba wyższy priorytet, wykona wcześniej
     * Niższa liczba niższy priorytet, wykona później.
     *
     * @return int
     */
    public function getPrior()
    {
        return 1000;
    }
    public function install(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<error>install method not implemented</error>');
    }
}
