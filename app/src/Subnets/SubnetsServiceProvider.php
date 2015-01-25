<?php namespace Hostbase\Subnets;

use Hostbase\Subnets\Finder\ElasticsearchSubnetsFinder;
use Hostbase\Subnets\Finder\SubnetsFinder;
use Hostbase\Subnets\Repository\CouchbaseSubnetRepository;
use Hostbase\Subnets\Repository\SubnetRepository;
use Illuminate\Support\ServiceProvider;
use Route;


/**
 * Class SubnetsServiceProvider
 * @package Hostbase\Subnets
 */
class SubnetsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        Route::resource('subnets', SubnetsController::class);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind(SubnetRepository::class, CouchbaseSubnetRepository::class);
        $this->app->bind(SubnetsFinder::class, ElasticsearchSubnetsFinder::class);
    }
}