#!/bin/bash

# @author Szymon Działowski 2014-01-05

## tools tools tools vvvvvvvvvvvvvvvvvvv
function argExist {
    # funkcja sprawdza czy istnieje podany argument "dwa" na liście typu "jeden dwa trzy cztery"
    # użycie :
    # LO=$(argExist test $@)  # sprawdza czy istnieje argument 'test' w argumentach skryptu wywołanego "/bin/bash test.sh  afdsa test ddsf"
    # echo $LO   # rezultat: "true"
    for i in ${@:2}  # iteruje po wszystkich argumentach oprócz pierwszego
    # uwaga jest różnica pomiędzy $@ a $* http://stackoverflow.com/a/256225
    do
        #echo "-=$i=-" # test
        if [ "$i" == "$1" ]; then
            echo "true"
            exit
        fi
    done
    echo "false"
}
## tools tools tools ^^^^^^^^^^^^^^^^^^^
THISFILE=${BASH_SOURCE[0]}
DIR="$( cd "$( dirname "${THISFILE}" )" && pwd -P )"
cd $DIR;
# tworze unikatowy klucz dla tego projektu którego będę szukał w ps aux
LOCK="lock-$(echo $(pwd -P) | sha1sum | sed -r 's#([^0-9]+)##g' | sed -r 's#^(.{5}).*#\1#')"
#echo $LOCK
if [ "$(argExist $LOCK $@)" == "true" ]; then # to już jest funkcja wywołana w drugim przebiegu
    shift #obrąbuję pierwszy argument
##### logika z wykluczeniem vvvv #### vvvv #### vvvv #### vvvv #### vvvv #### vvvv #### vvvv #### vvvv #### vvvv ####


    SERVER="$(ps aux | grep 'gulp' | grep -v grep | awk '{print $2}')";

    if [ ${SERVER} ]; then
        echo "Stop BrowserSync"
        kill ${SERVER}
    fi


    #PHP="php -c php.ini "
    PHP="php "
    NOW="$(date +%Y-%m-%d_%H-%M-%S)"

    rm -rf ${DIR}/web/js/* ${DIR}/web/asset/* ${DIR}/app/logs/* ${DIR}/app/cache/dev/* ${DIR}/app/cache/prod/*

    # ${PHP} ${DIR}/app/console fos:js-routing:dump
    ${PHP} ${DIR}/app/console cache:clear --env=prod
    ${PHP} ${DIR}/app/console assets:install ${DIR}/web --symlink

    # sudo setfacl -dR -m u:$(ps aux | grep apache | grep -v root | grep -v color | tail -2 | head -1 | cut -d " " -f1):rwx -m u:$(whoami):rwx app/cache app/logs
    # php vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php

#    setfacl -dR -m u:$(ps aux | grep apache | grep -v root | grep -v color | tail -2 | head -1 | cut -d " " -f1):rwx -m u:user:rwx app/cache app/logs



        # SERVER="$(ps aux | grep 'gulp' | grep -v grep | awk '{print $2}')";

        # if [ ${SERVER} ]; then
            # printf "Restart"
            # kill ${SERVER}
        # else
            # printf "Run"
        # fi

        # if [ "$(whoami)" == "root" ]; then
            # echo -e " BrowserSync\n\n";
            # node node_modules/gulp/bin/gulp.js server & disown
        # fi

cd web
kill $(ps aux | grep 'gulp' | grep -v grep | awk '{print $2}') || echo 'nie jest odpalony gulp' && node node_modules/gulp/bin/gulp.js serve &

echo "sleep -----------'
sleep 4;

echo 'touch -----------'
touch bundles/app/front/scss/app.scss

cd ..

${PHP} ${DIR}/app/console assetic:dump --env=prod




##### logika z wykluczeniem ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ #### ^^^^ ####
else # tutaj wywołanie w pierwzym przebiegu
    #echo "Skrypt cache pierwszy poziom"
    # sprawdzam czy już jest uruchomiony skrypt
    if [ $(ps aux | grep -c "$LOCK") -gt 1 ]; then   # jest już, więc wychodzę z komunkatem że nie uruchamiam drugi raz dopóki pierwszy proces się nie skończy
        echo "Cache jest w tej chwili czyszczony - wzajemne wykluczenie";
    else                                             # nie ma więc uruchamiam ten sam skrypt ale dorzucam na początku argument "LOCK"
        #echo "Skrypt cache drugi poziom"
        /bin/bash $THISFILE $LOCK $@
    fi
fi