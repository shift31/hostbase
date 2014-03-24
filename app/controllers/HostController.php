<?php

use Hostbase\Host\HostRepository;
use Hostbase\Entity\EntityTransformer;
use League\Fractal\Manager;


class HostController extends ResourceController
{

    /**
     * @param HostRepository $hosts
     * @param Manager $fractal
     * @param Hostbase\Entity\EntityTransformer $transformer
     */
    public function __construct(HostRepository $hosts, Manager $fractal, EntityTransformer $transformer)
    {
        $this->repository = $hosts;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 