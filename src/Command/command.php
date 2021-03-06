<?php

// php vendor/stopsopa/utils/src/Stopsopa/UtilsBundle/Command/command.php

set_time_limit(0);

require_once dirname(__FILE__).'/../../../../../../autoload.php';

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Application;
use Stopsopa\UtilsBundle\Composer\UtilHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Stopsopa\UtilsBundle\Exception\NoFrameworkException;

$input = new ArgvInput();
$console = new Application();

$d = DIRECTORY_SEPARATOR;

// zastąpić później preg_quote($str);
$dd = $d;
if ($d === '\\') {
    $dd .= $d;
}

try {
    foreach (UtilHelper::findClasses("#{$d}Command$dd.*Command\.php$#", '#\Command\\\\.+Command$#', '#(Abstract)#') as $namespace) {
        $cmd = new $namespace();
        if ($cmd instanceof Command) {
            $console->add($cmd);
        }
    }
} catch (NoFrameworkException $ex) {
    throw $ex;
    if ($ex->getCode() === NoFrameworkException::INAPPROPRIATE_USE) {
        die("Używaj konsoli z poziomu Symfony2: php console stpa:install\n");
    }

    throw $ex;
}

if (strpos($_SERVER['PHP_SELF'], 'Stopsopa/UtilsBundle/Command') !== false) {
    $output = new ConsoleOutput();
    $output->writeln("<fg=magenta>Tip: Create script named 'console' in main directory of project:</fg=magenta>");
    $output->writeln('');
    $output->writeln('<fg=magenta>  #!/bin/php</fg=magenta>');
    $output->writeln('<fg=magenta>  <?php</fg=magenta>');
    $output->writeln("<fg=magenta>  require_once 'vendor/stopsopa/utils/src/Stopsopa/UtilsBundle/Command/command.php';</fg=magenta>");
    $output->writeln('');
    $output->writeln("<fg=magenta>and use like 'php console [command]'</fg=magenta>");
}

$console->run();
