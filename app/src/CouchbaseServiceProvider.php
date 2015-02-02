<?php namespace Hostbase;

use CouchbaseCluster;
use Illuminate\Support\ServiceProvider;


/**
 * Class CouchbaseServiceProvider
 * @package Hostbase
 */
class CouchbaseServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $app = $this->app;

        $this->app->singleton('CouchbaseBucket', function() use ($app) {
            /** @var CouchbaseCluster $couchbaseCluster */
            $couchbaseCluster = $app->make('CouchbaseCluster');
            return $couchbaseCluster->openBucket('hostbase');
        });
    }


    /**
     * @inheritdoc
     * @todo support cluster config
     */
    public function register()
    {
        $this->app->instance('CouchbaseCluster', new CouchbaseCluster);
    }
}