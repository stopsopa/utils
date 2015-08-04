<?php

namespace Stopsopa\UtilsBundle\Services;

use Exception;
use Stopsopa\UtilsBundle\Lib\AbstractApp;

/**
 * Stopsopa\UtilsBundle\Services\GeneratePassword.
 */
class GeneratePassword
{
    const SERVICE = 'password.generator';
    protected $words;
    protected $count;
    public function __construct($words = null)
    {
        if (!$words) {
            $words = include AbstractApp::getRootDir().'/app/config/words.php';
        }

        $this->words = $words;
        $this->count = count($words);
    }
    public function generate()
    {
        if (!$this->words) {
            throw new Exception('No list of words specified');
        }

        $password = $this->_randomWord();

        do {
            $tmp = $this->_randomWord();
        } while ($password === $tmp);

        $password .= $tmp;

        $password .= rand(0, 999);

        return $password;
    }
    protected function _randomWord()
    {
        return $this->words[rand(0, $this->count - 1)];
    }
}
