#!/bin/sh

# see https://docs.travis-ci.com/user/languages/php#Apache-%2B-PHP

sudo apt-get update
sudo apt-get install -y apache2 libapache2-mod-fastcgi

# enable php-fpm
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure apache virtual hosts
sudo cp -f .ci/travis/apache.conf /etc/apache2/sites-available/default
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default

echo "***** Apache Configuration *****"
cat /etc/apache2/sites-available/default

echo "***** Restarting Apache *****"
sudo service apache2 restart
