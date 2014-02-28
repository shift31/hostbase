# Hostbase
**A highly scalable host and network database with full-text search and a RESTful API**

- [Overview](#overview)
    - [Technology](#technology)
- [Installation](#installation)
    - [Development Server (Vagrant)](#development-server-vagrant)
- [Configuration](#configuration)
- [Usage](#usage)
    - [REST API endpoints](#rest-api-endpoints)
    - [Command Line Interface](#command-line-interface)
    - [Importers](#importers)
    - [API Client Library for PHP](#api-client-library-for-php)
- [Security](#security)
- [To-do](#to-do)

## Overview

Hostbase is a systems and network administration tool for cataloging hosts, subnets, and IP addresses.  It is designed to support private or public cloud operations of any size.  Whether you have a few dozen or a few thousand hosts across multiple environments and data centers, Hostbase can provide the foundation of a service-oriented architecture for tracking the lifecycle of hosts and networks.  Instead of storing duplicate host information across your continuous integration server, deployment tools, provisioning system, or CMDB, Hostbase can provide a single, centralized interface for storing and retrieving this information dynamically.  This is especially useful in environments where scaling horizontally is commonplace.

### Technology

Hostbase uses Couchbase Server to easily store data with whatever schema you choose, so you're not locked in to any particular data models other than the primary concepts of hosts, subnets, and IPs.  Hosts, subnets, and IPs are stored as JSON documents in a single Couchbase bucket.  Using the Couchbase plug-in for Elasticsearch, data is streamed in real-time from Couchbase to Elasticsearch.  This allows for full-text search using [Elasticsearch/Lucene query string syntax](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html).  So, for example, if you "tag" your servers by role, environment, and data center, a query string might be as simple as `env:qa AND role:web_server AND datacenter:dallas01`, to retrieve a list of all servers of the 'web_server' role, in the 'QA' environment, in the 'dallas01' data center.

In addition to Couchbase and Elasticsearch, Hostbase requires a web server with PHP 5.4 or greater.  All search and CRUD operations are exposed through a RESTful web service.

_**Hostbase is currently under heavy development, however it is quite usable.  Developers familiar with PHP and the Laravel framework are welcome to contribute by creating Pull Requests.**_

## Installation

_A rough overview (everything on a single machine)..._

1. Download and install [Couchbase Server](http://www.couchbase.com/download)
2. On your Couchbase server:
    - Create (or keep) the 'default' bucket.  This will be used for sessions / cache.
    - Create a bucket called 'hostbase' (you can call it whatever you want, but the default config supports this naming convention)
3. Download and install [Elasticsearch 0.90.5](http://www.elasticsearch.org/downloads/page/2/).  (Yes, ES 1.0 is out, but the Couchbase Plug-in hasn't been updated yet.)
4. On your Elasticsearch server, create an index called 'hostbase' with at least 1 shard...replicas are optional but recommended. The data can always be re-indexed by replicating from Couchbase again.
5. Install the [Couchbase Plug-in for Elasticsearch](http://www.couchbase.com/couchbase-server/connectors/elasticsearch)
6. Configure Couchbase XDCR to replicate the 'hostbase' bucket to the Elasticsearch cluster
7. Install PHP 5.4 and the web server of your choice (tested with Apache)
8. Install the [Couchbase PHP Client Library](http://www.couchbase.com/communities/php/getting-started)
9. Download/clone this whole repository to the directory of your choice, and configure your web server to serve it as you would any other Laravel-based project.  See http://laravel.com/docs/installation for more info.  You can also download it with [Composer](http://getcomposer.org) by running `composer create-project shift31/hostbase -s dev`.
10. From the project root, run: `composer install`

Optional, but recommended, download the [CLI](https://github.com/shift31/hostbase-cli) ([PHAR](https://github.com/shift31/hostbase-cli/raw/master/hostbase.phar))

### Development Server (Vagrant)

**For development or trial purposes, save yourself some installation time and use [Vagrant](http://vagrantup.com).  _You'll need 1 GB free RAM._**

```bash
git clone https://github.com/shift31/hostbase.git
cd hostbase
vagrant up
vagrant ssh
cd /vagrant
composer install
```

## Configuration

See http://laravel.com/docs/configuration for background information on configuring a Laravel-based project.

1. Edit app/config/app.php accordingly.
2. Edit app/config/database.php with your Couchbase server info (if you're running Couchbase on a separate host).
3. Edit app/config/elasticsearch.php as needed.  See https://github.com/shift31/laravel-elasticsearch for details.
4. If you don't want to use Couchbase's memcached functionality for sessions/cache, edit app/config/session.php and app/config/cache.php accordingly.

## Usage

There's no web UI or bulk raw data (JSON, CSV) import tool yet. So if you have a lot of hosts, your best bet is to use the PHP Client Library and write your own importer.  Feel free to explore the importers below for examples.

### REST API endpoints

| URI                                | Action                      | Comments                       |
| ---------------------------------- | --------------------------- | ------------------------------ |
| GET hosts                          | HostController@index        | Lists all hosts                |
| POST hosts                         | HostController@store        |                                |
| GET hosts/{host}                   | HostController@show         |                                |
| PUT hosts/{host}                   | HostController@update       |                                |
| PATCH hosts/{host}                 | HostController@update       |                                |
| DELETE hosts/{host}                | HostController@destroy      |                                |
| GET subnets                        | SubnetController@index      | Lists all subnets              |
| POST subnets                       | SubnetController@store      |                                |
| GET subnets/{subnet}               | SubnetController@show       |                                |
| PUT subnets/{subnet}               | SubnetController@update     |                                |
| PATCH subnets/{subnet}             | SubnetController@update     |                                |
| DELETE subnets/{subnet}            | SubnetController@destroy    |                                |
| GET ipaddresses                    | IpAddressController@index   | Lists all IP addresses         |
| POST ipaddresses                   | IpAddressController@store   |                                |
| GET ipaddresses/{ipaddress}        | IpAddressController@show    |                                |
| PUT ipaddresses/{ipaddress}        | IpAddressController@update  |                                |
| PATCH ipaddresses/{ipaddress}      | IpAddressController@update  |                                |
| DELETE ipaddresses/{ipaddress}     | IpAddressController@destroy |                                |

- Must receive JSON ('Host' header should be 'Content-type: application/json')
    - /hosts
        - Example:

            ```json
            {
                "fqdn": "hostname.domain.tld",
                "hostname": "hostname",
                "domain": "domain.tld",
                "adminCredentials": {
                    "username": "admin_username",
                    "password": "admin_password"
                }
            }
            ```
        - Required fields:
            - fqdn
            - hostname (automatically generated from FQDN if not specified)
            - domain (automatically generated from FQDN if not specified)
        - Special (optional) fields:
            - adminCredentials - Passwords will be encrypted prior to storage in the database, and decrypted on retrieval via the API
    - /subnets
        - Example:

            ```json
            {
                "network": "10.0.0.0",
                "netmask": "255.255.255.0",
                "gateway": "10.0.0.254",
                "cidr": "24"
            }
            ```
        - Required fields:
            - network
            - netmask
            - gateway
            - cidr
    - /ipaddresses
        - Example:

            ```json
            {
                "subnet": "10.0.0.0/24",
                "ipAddress": "10.0.0.1",
                "host": "hostname.domain.tld"
            }
            ```
        - Required fields:
            - subnet
            - ipAddress
- Search, where the 'q' parameter is an elasticsearch/lucene query string:
    - /hosts?q=
    - /subnets?q=
    - /ipaddresses?q=
    - Other parameters:
        - limit (defaults to 10,000; sets the 'size' of Elasticsearch results)
        - showData ('1' = true [default], '0' = false)

#### cURL examples

_Todo_ ... using the [CLI](#command-line-interface) is easier anyway

##### Store a host

##### Show a host

##### Update a host

##### Delete a host

### Command Line Interface

The CLI leverages the [API client library](#api-client-library-for-php), so you can administer your Hostbase server from anywhere PHP is installed.

https://github.com/shift31/hostbase-cli

### Importers

- PuppetDB: https://github.com/shift31/hostbase-importer-puppetdb
- SoftLayer: https://github.com/shift31/hostbase-importer-softlayer

### API Client Library for PHP

https://github.com/shift31/hostbase-api-client-php

## Security

_**If your host and network data is sensitive, it is up to you to provide the firewalls, VPNs, and associated authentication methods to protect your data.**_

Basic authentication will be implemented soon, but until then it is recommended (at the very least) to only operate Hostbase server/client on a private network.

## To-do

- Implement HTTP Basic Authentication with users stored in Hostbase and/or LDAP
- Tests (unit, integration, etc.)
- More Documentation and a video demo
- Puppet Module and Chef Cookbook to help automate installation
- Script to update Hostbase from Puppet Facts...perhaps using Facter output, run via Cron...or somehow during Puppet agent execution
- Puppet function to retrieve data from Hostbase (perhaps in addition to or in lieu of Hiera)
- Command line tool to aid initial configuration (driven by Laravel's artisan command)
- API
    - Pagination?
    - Bulk operations
- ...
