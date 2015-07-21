#!/bin/bash

echo -e "\e[33mStop gulp \e[0m"
kill -9 $(ps aux | grep 'gulp' | grep -v grep | awk '{print $2}') && echo -e "\e[31mGulp process killed\e[0m" || echo -e "\e[32mNo gulp process\e[0m"
