#!/bin/bash


if [ "$(id -u -n)" == "root" ]; then
    echo -e "\e[31mDon't run this script as root\e[31m"
else
    THISFILE=${BASH_SOURCE[0]}
    DIR="$( cd "$( dirname "${THISFILE}" )" && pwd -P )"
    cd $DIR;

    #PHP="php -c php.ini "
    PHP="php "
    NOW="$(date +%Y-%m-%d_%H-%M-%S)"


    /bin/bash stop.sh

    rm -rf ${DIR}/app/logs/* ${DIR}/app/cache/*

    node node_modules/gulp/bin/gulp.js sass-site

    node node_modules/gulp/bin/gulp.js sass-sp

    # ${PHP} ${DIR}/app/console fos:js-routing:dump
    ${PHP} ${DIR}/app/console cache:clear --env=prod
    ${PHP} ${DIR}/app/console assets:install ${DIR}/web --symlink
    ${PHP} ${DIR}/app/console assetic:dump --env=prod



    node node_modules/gulp/bin/gulp.js serve &


    # sudo setfacl -dR -m u:$(ps aux | grep apache | grep -v root | grep -v color | tail -2 | head -1 | cut -d " " -f1):rwx -m u:$(whoami):rwx app/cache app/logs
    # php vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php

    #    setfacl -dR -m u:$(ps aux | grep apache | grep -v root | grep -v color | tail -2 | head -1 | cut -d " " -f1):rwx -m u:user:rwx app/cache app/logs

fi

