#!/usr/bin/env bash

# htop
sudo apt-get -y install htop


# set permissions on storage
cd /vagrant && chmod -R +w app/storage


# run composer
cd /vagrant && composer install


# create apache vhost
export ETH0_ADDR=`ip -o -f inet addr | grep eth0 | awk '{print $4}' | rev | cut -c 4- | rev`
sudo vhost -d /vagrant/public -s "hostbase.${ETH0_ADDR}.xip.io"
sudo sed -i "s@#ProxyPassMatch.*@ProxyPassMatch ^/(.*\\\.php(/.*)?)$ fcgi://127.0.0.1:9000"/vagrant/public"/\$1@" /etc/apache2/sites-available/hostbase.${ETH0_ADDR}.xip.io.conf

export ETH1_ADDR=`ip -o -f inet addr | grep eth1 | awk '{print $4}' | rev | cut -c 4- | rev`
sudo vhost -d /vagrant/public -s "hostbase.${ETH1_ADDR}.xip.io"
sudo sed -i "s@#ProxyPassMatch.*@ProxyPassMatch ^/(.*\\\.php(/.*)?)$ fcgi://127.0.0.1:9000"/vagrant/public"/\$1@" /etc/apache2/sites-available/hostbase.${ETH1_ADDR}.xip.io.conf

sudo service apache2 reload