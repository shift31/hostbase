#!/bin/bash

# htop
sudo apt-get -y install htop


# couchbase and elastic search config
cd /vagrant && chmod +x cbes-config.sh && ./cbes-config.sh


# run composer
cd /vagrant && composer install


# create apache vhost
#sudo vhost -d /vagrant/public -s hostbase.192.168.33.10.xip.io

export ETH0_ADDR=`ip -o -f inet addr | grep eth0 | awk '{print $4}' | rev | cut -c 4- | rev`
sudo vhost -d /vagrant/public -s "hostbase.${ETH0_ADDR}.xip.io"

export ETH1_ADDR=`ip -o -f inet addr | grep eth1 | awk '{print $4}' | rev | cut -c 4- | rev`
sudo vhost -d /vagrant/public -s "hostbase.${ETH1_ADDR}.xip.io"