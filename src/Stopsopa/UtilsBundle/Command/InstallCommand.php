<?php

namespace Stopsopa\UtilsBundle\Command;

use Stopsopa\UtilsBundle\Composer\AbstractInstallerPart;
use Stopsopa\UtilsBundle\Composer\UtilHelper;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilIni;
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
        var_dump(AbstractApp::getStpaConfig());
        die();
        foreach ($this->_findPartsClasses() as $cls) {
            /* @var $cls AbstractInstallerPart */
            $cls->install($input, $output);
        }
        
        $output->writeln('dalsze rzeczy');
    }
    protected function _findPartsClasses() {
        $list = array();
        
        $d = DIRECTORY_SEPARATOR;
        $dd = $d;
        if ($d === '\\') 
            $dd .= $d;            
        
        foreach (
            UtilHelper::findClasses("#{$dd}InstallerPart\.php$#", '#\\InstallerPart$#', '#Abstract#') as $namespace
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
