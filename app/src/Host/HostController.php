<?php namespace Hostbase\Host;

use Hostbase\ResourceController;
use Hostbase\Entity\EntityTransformer;
use League\Fractal\Manager;


class HostController extends ResourceController
{

    /**
     * @param HostService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(HostService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 