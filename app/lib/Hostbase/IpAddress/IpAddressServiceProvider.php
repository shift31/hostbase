<?php namespace Hostbase\IpAddress;

use Illuminate\Support\ServiceProvider;


class IpAddressServiceProvider extends ServiceProvider {


    public function register()
    {
        $this->app->bind('Hostbase\IpAddress\IpAddressInterface', 'Hostbase\IpAddress\CouchbaseElasticsearchIpAddress');
    }
}