<?php namespace Hostbase\IpAddress;

use Illuminate\Support\ServiceProvider;


class IpAddressServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->bind('Hostbase\IpAddress\IpAddressRepository', 'Hostbase\IpAddress\CouchbaseIpAddressRepository');
        $this->app->bind('Hostbase\IpAddress\IpAddressFinder', 'Hostbase\IpAddress\ElasticsearchIpAddressFinder');
    }
}