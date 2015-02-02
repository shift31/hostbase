#!/usr/bin/env bash

echo ">>> Configuring Couchbase Plug-in for Elasticsearch"

sudo service elasticsearch stop

# install and configure couchbase plugin for elasticsearch
sudo /usr/share/elasticsearch/bin/plugin -install transport-couchbase -url http://packages.couchbase.com.s3.amazonaws.com/releases/elastic-search-adapter/2.0.0/elasticsearch-transport-couchbase-2.0.0.zip
echo "couchbase.username: Administrator"  | sudo tee -a /etc/elasticsearch/elasticsearch.yml
echo "couchbase.password: password"  | sudo tee -a /etc/elasticsearch/elasticsearch.yml

sudo service elasticsearch start
sleep 20

# import index template to define the scope of indexing and searching
curl -XPUT http://localhost:9200/_template/couchbase -d @/usr/share/elasticsearch/plugins/transport-couchbase/couchbase_template.json
sleep 1

# create empty index
curl -XPUT http://localhost:9200/hostbase
sleep 1

# change the number of concurrent requests elasticsearch will handle
echo "couchbase.maxConcurrentRequests: 1024" | sudo tee -a /etc/elasticsearch/elasticsearch.yml

sudo service elasticsearch restart
sleep 20


# configure couchbase
/opt/couchbase/bin/couchbase-cli cluster-init -c localhost:8091 -u Administrator -p password \
       --cluster-username=Administrator \
       --cluster-password=password \
       --cluster-port=8091 \
       --cluster-ramsize=256

/opt/couchbase/bin/couchbase-cli bucket-create -c localhost:8091 -u Administrator -p password \
       --bucket=default \
       --bucket-type=couchbase \
       --bucket-ramsize=128 \
       --bucket-replica=0 \
       --enable-index-replica=0 \
       --enable-flush=1

/opt/couchbase/bin/couchbase-cli bucket-create -c localhost:8091 -u Administrator -p password \
       --bucket=hostbase \
       --bucket-type=couchbase \
       --bucket-ramsize=128 \
       --bucket-replica=0 \
       --enable-index-replica=0 \
       --enable-flush=1

/opt/couchbase/bin/couchbase-cli setting-xdcr -c localhost:8091 -u Administrator -p password --max-concurrent-reps=8

/opt/couchbase/bin/couchbase-cli xdcr-setup -c localhost:8091 -u Administrator -p password  \
        --create \
        --xdcr-cluster-name=elasticsearch \
        --xdcr-hostname=localhost:9091 \
        --xdcr-username=Administrator \
        --xdcr-password=password

sleep 5

/opt/couchbase/bin/couchbase-cli xdcr-replicate -c localhost:8091 -u Administrator -p password \
        --create \
        --xdcr-cluster-name=elasticsearch \
        --xdcr-from-bucket=hostbase \
        --xdcr-to-bucket=hostbase \
        --xdcr-replication-mode=capi