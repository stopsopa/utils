#!/bin/bash


if [ "$(id -u -n)" == "root" ]; then
    echo -e "\e[31mDon't run this script as root\e[31m"
else

    THISFILE=${BASH_SOURCE[0]}
    DIR="$( cd "$( dirname "${THISFILE}" )" && pwd -P )"
    PHP="php "
    NOW="$(date +%Y-%m-%d_%H-%M-%S)"

    ${PHP} ${DIR}/app/console stpa:switch blank app/CommonTools.php true

    echo "czyszczenie katalogów cache & logs"

    /bin/bash stop.sh

    git branch

    git checkout .

    if [ "$(whoami)" == "this.domain" ]; then
        git clean -df
    else
        git reset --hard HEAD
    fi

    git pull

    /bin/bash clean.sh

    echo "na koniec odpalam gulp"
fi


