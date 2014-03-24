<?php namespace Hostbase\Host;

use Illuminate\Support\ServiceProvider;


class HostServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->bind('Hostbase\Host\HostRepository', 'Hostbase\Host\CbEsHostRepository');
    }
}