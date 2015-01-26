<?php namespace Hostbase\IpAddresses;

use Hostbase\IpAddresses\Finder\ElasticsearchIpAddressesFinder;
use Hostbase\IpAddresses\Finder\IpAddressesFinder;
use Hostbase\IpAddresses\Repository\CouchbaseIpAddressRepository;
use Illuminate\Support\ServiceProvider;
use Hostbase\IpAddresses\Repository\IpAddressRepository;
use Route;


/**
 * Class IpAddressesServiceProvider
 * @package Hostbase\IpAddresses
 */
class IpAddressesServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        Route::resource('ipaddresses', IpAddressesController::class);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(IpAddressRepository::class, CouchbaseIpAddressRepository::class);
        $this->app->singleton(IpAddressesFinder::class, ElasticsearchIpAddressesFinder::class);
    }
}