<?php

use Hostbase\Host\HostRepository;
use League\Fractal\Manager;


class HostController extends ResourceController
{

    /**
     * @param HostRepository $hosts
     * @param Manager        $fractal
     */
    public function __construct(HostRepository $hosts, Manager $fractal)
    {
        $this->resources = $hosts;
        $this->fractal = $fractal;
    }
} 