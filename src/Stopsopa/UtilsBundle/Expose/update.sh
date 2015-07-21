#!/bin/bash


if [ "$(id -u -n)" == "root" ]; then
    echo -e "\e[31mDon't run this script as root\e[31m"
else
    echo "czyszczenie katalogów cache & logs"

    /bin/bash stop.sh

    git branch

    git checkout .

    if [ "$(whoami)" == "area.test.absolvent.pl" ]; then
        git clean -df
    else
        git reset --hard HEAD
    fi

    git pull

    /bin/bash clean.sh

    echo "na koniec odpalam gulp"
fi


