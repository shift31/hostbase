<?php namespace Hostbase\IpAddress;

use Hostbase\ResourceController;
use Hostbase\Entity\EntityTransformer;
use League\Fractal\Manager;


class IpAddressController extends ResourceController
{

    /**
     * @param IpAddressService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(IpAddressService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 