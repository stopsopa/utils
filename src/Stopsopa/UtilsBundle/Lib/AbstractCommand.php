<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\Console\Command\Command;

/**
 * 
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
 */
abstract class AbstractCommand extends Command {
    
}

