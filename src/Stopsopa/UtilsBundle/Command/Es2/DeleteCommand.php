<?php
namespace Stopsopa\UtilsBundle\Command\Es2;

use Stopsopa\UtilsBundle\Command\AbstractCommand;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Services\Elastic2\ElasticSearch2;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractCommand {
    public function configure()
    {
        $this
            ->setName('es2:delete')
            ->addOption('index', null, InputOption::VALUE_REQUIRED, 'If only one index then specify it name here', null)
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'If only one index then specify it name here', null)
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'If only one index then specify it name here', null)
        ;
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $man = AbstractApp::get('elastic2');
        /* @var $man ElasticSearch2 */

        $man->delete(
            $input->getOption('index'),
            $input->getOption('type'),
            $input->getOption('id'),
            $output
        );
    }
}