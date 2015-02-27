<?php

namespace Stopsopa\UtilsBundle\Command;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
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
        die(AbstractApp::getRootDir());
//        $this->init($input, $output, true);
        
        $output->writeln('go');
    }

}
