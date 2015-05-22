composer create-project symfony/framework-standard-edition runtime '2.5.*'


<h1>Helpfull tools</h1>
  - http://fabien.potencier.org/article/76/php-cs-fixer-finally-reaches-version-1-0

#### Instalacja

    composer          require "stopsopa/utils":"dev-master"
    php composer.phar require "stopsopa/utils":"dev-master"

#### Konfigurajca w Symfony2
Jeśli w ramach Symfony2 to dodać do AppKernel.php


        $bundles = array(
            ...
            new Stopsopa\UtilsBundle\UtilsBundle()
        );


#### Konfigurajca w trybie standalone
Odpalanie komendy

    php vendor/stopsopa/utils/src/Stopsopa/UtilsBundle/Command/command.php

wyrzuci instrukcje na ekran
 ! ! ! warto chyba zrobić tutaj żeby wyrzucało informację że command plik utworzy w root ale przedtem pożesz wskazać gdzie ma być root
w pliku stpaconfig.ini





#### stpaconfig.ini

Można sterować zachowaniem instalatora tego bundle
przez utworzenie pliku stpaconfig.ini w katalogu głównym projektu

#### InstallerPart.php

komenda console stpa:install
szuka w każdym bundle pliku InstallerPart.php rejestruje je i wykonuje jeden po drugim wdług
kolejności sterowanej przez wartości zwracane z metody ->getPrior()
Klasa musi extendować AbstractInstallerPart.php




### License

The MIT License (MIT)
Copyright (c) 2014 Szymon Działowski
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

