<?php namespace Hostbase\Hosts;

use Hostbase\Entity\EntityTransformer;
use Hostbase\Http\ResourceController;
use League\Fractal\Manager;


/**
 * Class HostsController
 *
 * @package Hostbase\Hosts
 */
class HostsController extends ResourceController
{

    /**
     * @param HostsService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(HostsService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 