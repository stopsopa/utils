<?php

namespace Stopsopa\UtilsBundle;
use Composer\Script\Event;

/**
 * Stopsopa\UtilsBundle\Init
 */
class Init {
    public static function post_archive_cmd(Event $event) {
        file_put_contents('log.log.log', $event->getName()."\n", FILE_APPEND);
        $event->getIO()->writeln($event->getName());
    }
}    