<?php
namespace Stopsopa\UtilsBundle\Command\Es2;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Services\Elastic2\ElasticSearch2;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateIndexesCommand extends ContainerAwareCommand {
    public function configure()
    {
        $this
            ->setName('es2:index:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'If only one index then specify it name here', null)
        ;
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $man = AbstractApp::get('elastic2');
        /* @var $man ElasticSearch2 */


        $host = $this->getContainer()->getParameter('elastic.host');

        $output->writeln("<info>Server: $host</info>");

        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion("Target server is: <info>$host</info>, do you want to continue? (y|n) : ", false, '/^(y|j)/i');

        if ($helper->ask($input, $output, $question)) {
            $man->populate($input->getOption('index'), $output);
        }
        else {
            $output->writeln("Aborted");
        }

    }
}