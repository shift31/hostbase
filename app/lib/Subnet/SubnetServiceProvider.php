<?php namespace Hostbase\Subnet;

use Illuminate\Support\ServiceProvider;


class SubnetServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->bind('Hostbase\Subnet\SubnetRepository', 'Hostbase\Subnet\CbEsSubnetRepository');
    }
}