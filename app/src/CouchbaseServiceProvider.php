<?php namespace Hostbase;

use Illuminate\Support\ServiceProvider;

/**
 * Class CouchbaseServiceProvider
 * @package Hostbase
 */
class CouchbaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $couchbaseCluster = new \CouchbaseCluster();
        $couchbaseBucket = $couchbaseCluster->openBucket('hostbase');

        $this->app->instance(\CouchbaseCluster::class, $couchbaseCluster);
        $this->app->instance(\CouchbaseBucket::class, $couchbaseBucket);
    }
}