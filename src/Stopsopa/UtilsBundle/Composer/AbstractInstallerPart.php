<?php

namespace Stopsopa\UtilsBundle\Composer;

class AbstractInstallerPart {  
    /**
     * Wyższa liczba wyższy priorytet
     * niższa liczba niższy priorytet
     * @return int
     */
    public function getPrior() {
        return 1000;
    }
    public function install () {
        
    }
}