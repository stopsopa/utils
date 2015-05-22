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
            ->setDescription("Instalator iterujący po wszystkich bundlach w namespace Stopsopa i szukający w nich znajdujących się klas ... dopsiac")
            ->addOption('list', null, null,'Porcja danych do przetworzenia: ')
        ;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // tworzenie katalogu głównego /app
        $app = AbstractApp::getStpaConfig('core.app');
        if (!file_exists($app)) {
            UtilFilesystem::checkDir(dirname($app), true);
            $output->writeln("<info>Tworzenie katalogu: </info><comment>$app</comment>");
            mkdir($app);
        }

        $list = $this->_findPartsClasses();

        if ($input->getOption('list') === false) {
            foreach ($list as $cls) {
                /* @var $cls AbstractInstallerPart */
                $cls->install($input, $output);
            }
        }
        else {
            $tmp = array();

            foreach ($list as $cls) {
                /* @var $cls AbstractInstallerPart */
                $tmp[] = str_pad($cls->getPrior(), 8, ' ', STR_PAD_LEFT).' : '.get_class($cls);
            }

            $output->writeln(implode("\n", $tmp));
        }
    }
    protected function _findPartsClasses() {
        $list = array();

        $d = DIRECTORY_SEPARATOR;

        // zastąpić później preg_quote($str);
        $dd = $d;
        if ($d === '\\')
            $dd .= $d;

        foreach (UtilHelper::findClasses("#{$dd}InstallerPart\.php$#", '#\\InstallerPart$#', '#Abstract#') as $namespace) {
            $cls = new $namespace();
            if ($cls instanceof AbstractInstallerPart) {
                $list[] = $cls;
            }
        }

        usort($list, function ($a, $b) {
            /* @var $cls AbstractInstallerPart */
            return $a->getPrior() < $b->getPrior();
        });

        return $list;
    }
}
