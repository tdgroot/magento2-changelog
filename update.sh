#!/bin/bash

if [ -d out ]; then
    rm -r out
fi

mkdir -p out
composer create-project -qn --repository-url=https://repo.magento.com/ magento/project-community-edition:$1 out

if [ -d Magento ]; then
    rm -r Magento
fi

mkdir Magento
mv out/vendor/magento/* Magento/

# Remove packages already versoned on Github
rm -r Magento/magento-coding-standard Magento/magento-composer-installer Magento/magento2-functional-testing-framework

# Cleanup magento2-base, most of this is irrelevant
cd Magento/magento2-base
rm -r .github app/code app/design app/i18n dev generated pub/static var vendor *.md *.txt *.sample .*.sample
cd -

# Remove tests, it clogs up the diff and Magento will remove it from the default product anyways.
rm -r Magento/*/Test

# php rename-directories.php
