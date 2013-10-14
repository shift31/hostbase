<?php

namespace Hostbase;


use Illuminate\Support\ServiceProvider;


class HostServiceProvider extends ServiceProvider {



    public function register()
    {
        $this->app->bind('Hostbase\HostInterface', 'Hostbase\HostImpl');
    }
}