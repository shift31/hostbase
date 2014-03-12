<?php

use Hostbase\IpAddress\IpAddressRepository;
use Hostbase\ResourceTransformer;
use League\Fractal\Manager;


class IpAddressController extends ResourceController
{

    /**
     * @param IpAddressRepository $ipAddresses
     * @param Manager $fractal
     * @param ResourceTransformer $transformer
     */
    public function __construct(IpAddressRepository $ipAddresses, Manager $fractal, ResourceTransformer $transformer)
    {
        $this->resources = $ipAddresses;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 