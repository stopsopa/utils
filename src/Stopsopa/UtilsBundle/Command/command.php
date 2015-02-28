<?php

// php vendor/stopsopa/utils/src/Stopsopa/UtilsBundle/Command/command.php

set_time_limit(0);

require_once dirname(__FILE__).'/../../../../../../autoload.php';

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Application;
use Stopsopa\UtilsBundle\Composer\UtilHelper;
use Symfony\Component\Console\Command\Command;

$input = new ArgvInput();
$console = new Application();

$d = DIRECTORY_SEPARATOR;
$dd = $d;
if ($d === '\\') 
    $dd .= $d; 

foreach (
    UtilHelper::findClasses("#{$d}Command$dd.*Command\.php$#", '#\Command\\\\.+Command$#', "#(Abstract)#") as $namespace
) {
    $cmd = new $namespace();
    if ($cmd instanceof Command) {
        $console->add($cmd);
    }
}

$console->run();

