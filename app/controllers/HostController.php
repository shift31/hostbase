<?php

use Hostbase\Host\HostRepository;
use Hostbase\ResourceTransformer;
use League\Fractal\Manager;


class HostController extends ResourceController
{

    /**
     * @param HostRepository $hosts
     * @param Manager $fractal
     * @param Hostbase\ResourceTransformer $transformer
     */
    public function __construct(HostRepository $hosts, Manager $fractal, ResourceTransformer $transformer)
    {
        $this->resources = $hosts;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 