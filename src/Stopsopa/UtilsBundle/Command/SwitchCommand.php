<?php

namespace Stopsopa\UtilsBundle\Command;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class SwitchCommand extends AbstractCommand
{
    public function configure()
    {
        $this
            ->setName('stpa:switch')
            ->setDescription('Przełącza flagi w plikach w formacie /*id*/!!1 na /*id*/!!0 i odwrotnie za pomocą stpa:switch')
            ->addArgument('id', InputArgument::REQUIRED, 'Id parametru do przełączenia')
            ->addArgument('file', InputArgument::REQUIRED, 'Plik w którym ma być przełączony parametr')
            ->addArgument('state', InputArgument::OPTIONAL, 'wymuś stan')
        ;
    }
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $id             = $input->getArgument('id');

        $file           = $input->getArgument('file');

        $state          = $input->getArgument('state');

        if (is_string($state)) {
            $state = strtolower(trim($state));
            switch ($state) {
                case '1':
                case 'true':
                    $state = true;
                    break;
                case '0':
                case 'false':
                    $state = false;
                    break;
                default:
                    throw new Exception("Allowed values in state are: 1, true, 0, false");
            }
        }

        UtilFilesystem::toggleFlag($file, $id, $state);

        echo "Current state: ".( UtilFilesystem::toggleFlagGetState($file, $id) ? 'true' : 'false')."\n";
    }
}
