<?php namespace Hostbase\Hosts;

use Hostbase\Hosts\Finder\ElasticsearchHostsFinder;
use Hostbase\Hosts\Finder\HostsFinder;
use Hostbase\Hosts\Repository\CouchbaseHostRepository;
use Hostbase\Hosts\Repository\HostRepository;
use Illuminate\Support\ServiceProvider;
use Route;


/**
 * Class HostsServiceProvider
 * @package Hostbase\Hosts
 */
class HostsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        Route::resource('hosts', HostsController::class);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(HostRepository::class, CouchbaseHostRepository::class);
        $this->app->singleton(HostsFinder::class, ElasticsearchHostsFinder::class);
    }
}