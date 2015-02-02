#!/usr/bin/env bash

echo ">>> Installing Couchbase Server"

# Set some variables
COUCHBASE_EDITION=community
COUCHBASE_VERSION=3.0.1 # Check http://http://www.couchbase.com/download/ for latest version
COUCHBASE_ARCH=amd64

wget --quiet http://packages.couchbase.com/releases/${COUCHBASE_VERSION}/couchbase-server-${COUCHBASE_EDITION}_${COUCHBASE_VERSION}-ubuntu12.04_${COUCHBASE_ARCH}.deb
sudo dpkg -i couchbase-server-${COUCHBASE_EDITION}_${COUCHBASE_VERSION}-ubuntu12.04_${COUCHBASE_ARCH}.deb
rm couchbase-server-${COUCHBASE_EDITION}_${COUCHBASE_VERSION}_${COUCHBASE_ARCH}.deb

php -v > /dev/null 2>&1
PHP_IS_INSTALLED=$?

dpkg -s php-pear
PEAR_IS_INSTALLED=$?

dpkg -s php5-dev
PHPDEV_IS_INSTALLED=$?

if [ ${PHP_IS_INSTALLED} -eq 0 ]; then

    if [ ${PEAR_IS_INSTALLED} -eq 1 ]; then
        sudo apt-get -qq install php-pear
    fi

    if [ ${PHPDEV_IS_INSTALLED} -eq 1 ]; then
        sudo apt-get -qq install php5-dev
    fi

    apt-get remove php5-xdebug
    pecl install xdebug

    sudo cat > /etc/php5/mods-available/xdebug.ini << EOF
; configuration for xdebug
; priority = 10
zend_extension=/usr/lib/php5/20121212/xdebug.so
EOF
    sudo php5enmod xdebug
    sudo service php5-fpm restart

    sudo wget --quiet -O/etc/apt/sources.list.d/couchbase.list http://packages.couchbase.com/ubuntu/couchbase-ubuntu1404.list
    wget --quiet -O- http://packages.couchbase.com/ubuntu/couchbase.key | sudo apt-key add -
    sudo apt-get update
    sudo apt-get -qq install libcouchbase2-core libcouchbase-dev libcouchbase2-bin libcouchbase2-libevent libcouchbase2-libev

    sudo pecl install couchbase
    sudo cat > /etc/php5/mods-available/couchbase.ini << EOF
; configuration for php couchbase module
; priority=30
extension=couchbase.so
EOF
    sudo php5enmod couchbase
    sudo service php5-fpm restart
fi