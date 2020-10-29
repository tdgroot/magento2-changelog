#!/bin/bash

if [ -d out ]; then
    rm -r out
fi

mkdir -p out
composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition:$1 out

if [ -d Magento ]; then
    rm -r Magento
fi

mkdir Magento
mv out/vendor/magento/* Magento/

php rename-directories.php
