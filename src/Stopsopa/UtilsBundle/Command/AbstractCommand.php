<?php

namespace Stopsopa\UtilsBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Cms\BaseBundle\Command\AbstractCommand
 */
abstract class AbstractCommand extends Command {

    /**
     * @var Connection
     */
    protected $dbal;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     *
     * @var integer 
     */
    protected $count;
// http://symfony.com/doc/current/components/console/introduction.html#coloring-the-output         
//$this->writeln("<error>czerwony</error> 
//<question>czarny na jasno niebieskim tle</question>
//<comment>żółty</comment>
//<info>zielony</info>
//
// złożona notacja:  
// kolory: 
//   black 
//   red(pomarańczowy jeśli użyty jako tekst, jeśli jako tło to czerwony) 
//   green 
//   yellow 
//   blue(ciem. niebies) 
//   magenta(róż) 
//   cyan(jas. niebies) 
//   white
// style: 
//   bold, 
//   underscore (czasem dodaje faktycznie podkreślenie a czasem zmienia odcień koloru, np red wreszcie staje się czerwony w tekście zamiast pomarańczowego), 
//   blink, 
//   reverse, 
//   conceal
// <fg=red;bg=cyan;options=bold>  -----   </fg=red;bg=cyan;options=bold>
//");
//
//    public function execute(InputInterface $input, OutputInterface $output) {
//      parent::execute($input, $output);
//
//
//        $this
//          ->setName('site:appdumper:allpois')
//          ->setDescription("Robi zrzut json wszystkich obiktów poi dla aplikacji mobilnych, patrz --help")->setHelp("
//Robi zrzut json wszystkich obiktów poi dla aplikacji mobilnych
//  php app/console site:appdumper:allpois count
//  php app/console site:appdumper:allpois count
//          ")
//          ->addOption('page',null,null,'Porcja danych do przetworzenia: ') - ta opcja jeśli chcemy zrobić przełącznik typu true/false, 
//                           wtedy odbieramy z obiektu input i juz mamy informację czy przełącznik został włączony czy nie

//  ->addOption('force', '-f', InputOption::VALUE_NONE, $description = "Wykonanie sql na bazie danych (domyślnie tylko zwraca sql)")
//            ->addArgument( // http://symfony.com/doc/current/components/console/introduction.html#creating-a-basic-command
//                'count',
//                InputArgument::OPTIONAL,
//                'Ile jest porcji danych?',
//                null
//            )
//          ;
//    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string|array $em
     */
    protected function init(InputInterface $input, OutputInterface $output, $em = null) {        
        
        $this->input   = $input;
        $this->output  = $output;
        
        if ($em === false) 
            return;
        
        if ($em === true)
            $em = 'default';

        if (is_null($em))
            throw new Exception("Ustaw najpierw parametr 'em' np: 'old' lub ustaw wartość 'true' aby wybrać domyślny manager. ".__METHOD__);      

        if (is_string($em)) {
            $this->em      = AbstractApp::get("doctrine.orm.{$em}_entity_manager");
            $this->dbal    = $this->em->getConnection();
        }
        if (is_array($em)) {
            $this->em      = array();
            $this->dbal    = array();
            foreach ($em as $d) {
                $this->em[$d]   = AbstractApp::get("doctrine.orm.{$d}_entity_manager");
                $this->dbal[$d] = $this->em[$d]->getConnection();
            }
        }

        /**
         * Przestawiam domyślny język
         */
//        try {
//  //          niechginiee('opt');
//  //          niechginie($input->getOption('lang'));
//            if ($lang = $input->getOption('lang'))
//                $this->getContainer()->parameters['locale'] = $lang;        
//        }
//        catch (InvalidArgumentException $e) {
//            if (strpos($e->getMessage(), ' option does not exist') !== false) {
//                echo $e->getMessage().PHP_EOL;
//                die("
//  Trzeba pdmienić ścieżkę do klasy w app/console z:
//  use Symfony\Bundle\FrameworkBundle\Console\Application;
//  na:
//  use Cms\BaseBundle\Classes\Application;
//  ");          
//            }
//        }
    }

    protected function _getOutput() {
      
        if (!$this->output) 
            throw new Exception("Before you user output first user \$this->init(\$input, \$output); in method ->execute() in this command class...");

        return $this->output;
    }

    protected function writeln($str) {
//      echo $str.PHP_EOL;
        $this->_getOutput()->writeln($str);
//      return $this;
    }
    protected function write($str) {
//      echo $str;
        $this->_getOutput()->write($str);
//      return $this;
    }
    /**
     * Szymon Działowski
     * @param int $count
     */
    public function setSum($count) {
        $this->count = $count;
    }
    /**
     * Szymon Działowski
     * @param string $num
     */
    protected $_i = 0;
    public function viewProgress($num, $length = 75, $signs = '[#-]') {
        if ($this->_i > 100000000) $this->_i = 0;
            $this->_i++;

        $percent = floor(($num / $this->count) * 100);
        $percent = str_pad($percent,     3, ' ', STR_PAD_LEFT);

        $count     = str_pad("$num/{$this->count}", 16, ' ', STR_PAD_LEFT);
        $k = '|/-\\';
        $this->write("\rPending: $count : ".$this->_buildBelt($percent,$length,$signs).' '.($k[($this->_i)%4])." $percent% ");
    }
    /**
     * Szymon Działowski
     * @param int $percent
     * @param int $length
     * @return string
     */
    protected function _buildBelt($percent,$length = 50,$signs = '[= ]') {
        $fill  = floor(($length/100)*$percent);
        $empty = ceil(($length/100)*(100-$percent));
        return trim($signs[0].str_repeat($signs[1], $fill).str_repeat($signs[2], $empty).$signs[3]);
    }

    public function getDialog() {
        $dialog = $this->getHelperSet()->get('dialog');
        
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') 
            $this->getHelperSet()->set($dialog = new DialogHelper());        

        return $dialog;
    }

}