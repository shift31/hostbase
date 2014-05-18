<?php namespace Hostbase\Host;

use Hostbase\ResourceController;
use Hostbase\Entity\EntityTransformer;
use League\Fractal\Manager;


class HostController extends ResourceController
{

    /**
     * @param HostService $hosts
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(HostService $hosts, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $hosts;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 