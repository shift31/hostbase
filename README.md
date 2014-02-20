# Hostbase
**A highly scalable host and network database with full-text search and a RESTful API**

## Overview

Hostbase is a systems and network administration tool for keeping track of hosts, subnets, and IP addresses.  It is designed to support private or public cloud operations of any size.  If you have a few dozen (or even thousands) of servers across multiple environments and data centers, Hostbase can provide the foundation of a service-oriented architecture for tracking the lifecycle of servers and networks.  Instead of storing duplicate server information across your continuous integration server, deployment tools, and provisioning system, Hostbase can provide a single, centralized interface for retrieving this information dynamically.  This is especially useful in environments where scaling horizontally is commonplace.  Even if you don't have so many servers, it's nice to be able to keep track of everything in one place.

### Technology

Hostbase uses Couchbase Server to store data in a completely schema-less fashion, so you're not locked in to any particular data model other than the primary concepts of hosts, subnets, and IPs.  Using the Couchbase plug-in for Elasticsearch, data becomes almost instantly searchable as it's persisted to Couchbase.  All fields can be searched using [Elasticsearch/Lucene query string syntax](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html).  So, for example, if you "tag" your servers by role, environment, and data center, a query string might be as simple as `env:qa AND role:web_server AND datacenter:dallas01`, to retrieve a list of all servers of the 'web_server' role, in the 'QA' environment, in the 'dallas01' data center.

In addition to Couchbase and Elasticsearch, Hostbase requires a web server with PHP 5.4 or greater.  All search and CRUD operations are exposed through a RESTful web service.

_**Hostbase is currently under heavy development and should be considered to be in an alpha state, however it is quite usable.  Developers familiar with PHP and the Laravel framework are welcome to contribute by creating Pull Requests.**_

## Installation

_A rough overview..._

1. Download and install [Couchbase Server](http://www.couchbase.com/download)
2. On your Couchbase server, create a bucket called 'hostbase' (you can call it whatever you want, but the default config supports this naming convention)
3. Download and install [Elasticsearch 0.90.5](http://www.elasticsearch.org/downloads/page/2/).  (Yes, ES 1.0 is out, but the Couchbase Plug-in hasn't been updated yet, and the versions must match.)
4. On your Elasticsearch server, create an index called 'hostbase' with at least 1 shard...replicas are optional but recommended. The data can always be re-indexed by replicating from Couchbase again.
5. Install the [Couchbase Plug-in for Elasticsearch](http://www.couchbase.com/couchbase-server/connectors/elasticsearch)
6. Configure Couchbase XDCR to replicate the 'hostbase' bucket to the Elasticsearch cluster
7. Install PHP 5.4 and the web server of your choice (tested with Apache)
8. Install the [Couchbase PHP Client Library](http://www.couchbase.com/communities/php/getting-started)
9. Download/clone this whole repository to the directory of your choice, and configure your web server to serve it as you would any other Laravel-based project.  See http://laravel.com/docs/installation for more info.  You can also download it with [Composer](http://getcomposer.org) by running `composer create-project shift31/hostbase`.
10. From the project root, run: `composer install`

Optional, but recommended, download the [CLI](https://github.com/shift31/hostbase-cli) ([PHAR](https://github.com/shift31/hostbase-cli/raw/master/hostbase.phar))

### Vagrant

For testing purposes, save yourself some installation time and use [Vagrant](http://vagrantup.com).  **You'll need 1GB free RAM.**

1. `composer create-project shift31/hostbase`
2. `vagrant up`

## Configuration

See http://laravel.com/docs/configuration for background information on configuring a Laravel-based project.

1. Edit app/config/app.php accordingly
2. Edit app/config/database.php with your Couchbase server info (if you're running on a separate host)
3. Edit app/config/elasticsearch.php as needed.  See https://github.com/shift31/laravel-elasticsearch for details.

## Usage

There's no web UI or bulk raw data (JSON, CSV) import tool yet. So if you have a lot of hosts, your best bet is to use the PHP Client Library and write your own importer.  Feel free to explore the importers below for examples.

### Command Line Interface

https://github.com/shift31/hostbase-cli

### Importers

- PuppetDB: https://github.com/shift31/hostbase-importer-puppetdb
- SoftLayer: https://github.com/shift31/hostbase-importer-softlayer

### PHP Client Library

https://github.com/shift31/hostbase-api-client-php

## To-do

- Implement HTTP Basic Authentication with users stored in Hostbase and/or LDAP
- Tests (unit, integration, etc.)
- More Documentation and a video demo
- Puppet Module and Chef Cookbook to help automate installation
- Script to update Hostbase from Puppet Facts...perhaps using Facter output, run via Cron...or somehow during Puppet agent execution
- Puppet function to retrieve data from Hostbase (perhaps in addition to Hiera)
- Command line tool to aid initial configuration (driven by Laravel's artisan command)
- API
    - Pagination
    - Bulk operations
- ...