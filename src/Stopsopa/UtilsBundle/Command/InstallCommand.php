<?php

namespace Stopsopa\UtilsBundle\Command;

use Stopsopa\UtilsBundle\Composer\AbstractInstallerPart;
use Stopsopa\UtilsBundle\Composer\ComposerHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends AbstractCommand {

    public function configure() {
        $this
            ->setName('stpa:install')
            ->setDescription("Instalato iterujący po wszystkich bundlach w namespace Stopsopa i szukający w nich znajdujących się klas ... dopsiac")
        ;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->_findPartsClasses() as $cls) {
            /* @var $cls AbstractInstallerPart */
            $cls->install($input, $output);
        }
        
        $output->writeln('go');
    }
    protected function _findPartsClasses() {
        $list = array();
        
        foreach (
            ComposerHelper::findClasses('InstallerPart') as $namespace
        ) {
            $cls = new $namespace();
            if ($cls instanceof AbstractInstallerPart) {
                $list[] = $cls;
            }
        }

        usort($list, function ($a, $b) {
            /* @var $cls AbstractInstallerPart */
            return $a->getPrior() > $b->getPrior();
        });

        return $list;
    }
}
