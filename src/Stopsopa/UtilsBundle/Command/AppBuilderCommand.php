<?php

namespace Stopsopa\UtilsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Command zajmuje się generowaniem klasy AppGenerated.php
 * Cms\BaseBundle\Command\AppBuilderCommand.
 */
class AppBuilderCommand extends AbstractCommand
{
    protected $app = '/AppExt.php'; // śieżka gdzie konkretnie ma się znajdować plik rozszerzający w bundlu
  protected $targetapp; // = '/src/Site/AbstractBundle/Lib/AppGenerated.php'; // ścieżka w projekcjie gdzie ma być wygenerowana klasa

  protected $_namespaces = array();
    protected $_constans = array();
    protected $_properties = array();
    protected $_methods = array();
    protected $_const_delim_start = '\/\*+\s*c\s*v\s*\*+\/';  //  /* c v */
  protected $_const_delim_stop = '\/\*+\s*c\s*\^\s*\*+\/'; //  /* c ^ */
  protected $_prop_delim_start = '\/\*+\s*p\s*v\s*\*+\/';  //  /* p v */
  protected $_prop_delim_stop = '\/\*+\s*p\s*\^\s*\*+\/'; //  /* p ^ */
  protected $_meth_delim_start = '\/\*+\s*m\s*v\s*\*+\/';  //  /* m v */
  protected $_meth_delim_stop = '\/\*+\s*m\s*\^\s*\*+\/'; //  /* m ^ */

  public function configure()
  {
      $this
          ->setName('stpa:appbuilder')
          ->setDescription('Komenda do budowy klasy zbiorczej AppGenerated.php')
//          ->setHelp("
//Przykłady użycia:
//  con site:settings:fixtures                                     -- dodaję dane z całej listy jeśli nie ma
//  con site:settings:fixtures --path=blocks.splash                -- dodaje jeden klucz jeśli nie ma
//  con site:settings:fixtures --path=block                        -- dodaje jeden węzeł głowny jeśli nie ma (bez dzieci)
//  con site:settings:fixtures --path=blocks.splash replace        -- zastępuję istniejący już klucz nowym wpisem z pliku
//  con site:settings:fixtures --path=blocks replace               -- zastępuję istniejący już klucz nowym wpisem z pliku
//")

//        ->setHelp(" 
// Przykłąd użycia - pusty przebieg
//   php app/console site:idg:links-update --csv=/home/site/linki.csv --idp=2 --idl=4
// Przykładu użycia - wprowadzenie danych do bazy
//   php app/console site:idg:links-update --csv=/home/site/linki.csv --idp=2 --idl=4 --force")
//        ->addOption('dump','-d',null,'Kieruje stdout do pliku...')
//        ->addArgument('replace', InputArgument::OPTIONAL, "Tryb nadpisywania wartości, normalnie jest dodawany tylko jeśli nie sitnieje, pomijany jeśli istnieje")
//        ->addOption('path', '-p', InputOption::VALUE_OPTIONAL, 
//"Można podać jaki konkretnie klucz ma być obsłużony, np: 
//  'gallery.maxsize' - tylko konkretny klucz
//  'gallery'         - wszystkie parametry galerii
//... reszta zostanie pominięta
//
//Domyślnie ten parametr to null, oznacza obsłuż wszystkie klucze
//", $default = null)
//        ->addArgument('list', InputArgument::OPTIONAL, "listuje klucze")


//        ->addOption('idp',null,null,'Numer kolumny z id\'kami programów')
//        ->addOption('idl',null,null,'Numer kolumny z linkami')
//        ->addOption('force',null,null,'Wprowadza dane do bazy')
        ;
  }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output, true);
        $dir = dirname($this->getContainer()->get('kernel')->getRootDir()).'/app';
        $this->targetapp = $dir.'/AppGenerated.php';
//      niechginie($this->targetapp);
//      $input->getOption('dump') and Misc::redirectIOToFilesByDir(); 
//      $this->init($input, $output, true);
      $list = $this->_findAppExtensionsFiles();

        foreach ($list as $file) {
            $data = file_get_contents($file);
            $namespace = $this->_cutFilepath($file);

        // wyciągam USE
        $this->_namespaces             += $this->_findNamespaces($data, $file);

        // wyciągam CONST
        $this->_constans[$namespace] = $this->_findParts($data, $this->_const_delim_start, $this->_const_delim_stop);

        // wyciągam PROPERTIES
        $this->_properties[$namespace] = $this->_findParts($data, $this->_prop_delim_start, $this->_prop_delim_stop);

        // wyciągam METHODS
        $this->_methods[$namespace] = $this->_findParts($data, $this->_meth_delim_start, $this->_meth_delim_stop);
        }

        $compiled = $this->_buildApp();

        $target = $this->targetapp;
        if (file_exists($target)) {
            unlink($target);
        }

        file_put_contents($target, $compiled, FILE_APPEND);

        $this->writeln("Kompilacja powiodła się, teraz pobierz do IDE plik z serwera... $target");
    }
    /**
     * @return ContainerInterface
     */
    public static function getCont()
    {
        if (!static::$_kernel) {
            global $kernel;
            static::$_kernel = $kernel;
            if ($kernel instanceof AppCache) {
                static::$_kernel = $kernel->getKernel()->getContainer();
            }
        }

        return static::$_kernel->getContainer();
    }
    protected static $_kernel;
    /**
     * Szuka w treści pliku namespace'y.
     *
     * @param type $data
     */
    protected function _findNamespaces($data, $file)
    {
        preg_match_all('#use\s+(?:\\\\)?([^\s;]+(?:\s+as\s+[^\s;]+)?);#i', $data, $matches);
        $list = array();
        if (isset($matches[1])) {
            foreach ($matches[1] as $n) {
                $n = trim(preg_replace('#\s+#', ' ', $n), ' ;');

                if (preg_match('# as #i', $n)) {
                    $n = preg_replace('# as #i', ' as ', $n);
                }

                $list[$n] = true;
            }
        }

        return $list;
    }
    /**
     * Wycina kawałki kodu z pliku AppExt.php oznaczone znacznikami:
     * /* c v * /, /* p v * /, /* m v * /.
     *
     * @param type $data
     * @param type $start
     * @param type $stop
     *
     * @return type
     */
    protected function _findParts($data, $start, $stop)
    {
        //    $start = CoreString::addSlashesPreg($start);
//    $stop  = CoreString::addSlashesPreg($stop);
//    niechginie("#$start(.*?)$stop#is");

      preg_match_all("#$start(.*?)$stop#si", $data, $matches);

        $list = array();
        if (isset($matches[1])) {
            $list = $matches[1];
        }

        return $list;
    }

    /**
     * Składa do kupy plik końcowy.
     *
     * @return type
     */
    protected function _buildApp()
    {
        $namespaces = '';
        foreach ($this->_namespaces as $namespace => $d) {
            $namespaces .= "\nuse $namespace;";
        }

        $namespaces .= "\n";

        return $this->getContainer()->get('templating')->render('CmsBaseBundle::app.html.twig', array(
        'namespaces' => $namespaces,
        'conststants' => $this->_constans,
        'properties' => $this->_properties,
        'methods' => $this->_methods,
      ));
    }

    protected function _cutFilepath($path)
    {
        return preg_replace('#^.*?\/(?:src|vendor)\/(.*)$#i', '$1', $path);
    }

    protected function _findAppExtensionsFiles()
    {
        $r = array();
        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
            /* @var $bundle \Sensio\Bundle\DistributionBundle\SensioDistributionBundle */
        $ext = $bundle->getPath().$this->app;

            if (file_exists($ext)) {
                $r[$bundle->getName()] = $ext;
            }
        }

        return $r;
    }

    /**
     * Zwraca ścieżkę do katalogu głównego projektu.
     *
     * @param bool $bundlepath - def: false, true - absolute path to current bundle
     *
     * @return string
     */
    public static function getRootDir($bundlepath = false)
    {
        $dir = dirname(static::getCont()->getParameter('kernel.root_dir'));

        if ($bundlepath) {
            $n = get_called_class();
            $n = substr($n, 0, -strlen(strrchr($n, '\\')));
            $n = substr($n, 0, -strlen(strrchr($n, '\\')));
            $n = str_replace('\\', '/', $n);
            $dir .= "/src/$n";
        }

        return $dir;
    }
}
