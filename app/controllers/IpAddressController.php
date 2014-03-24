<?php

use Hostbase\IpAddress\IpAddressRepository;
use Hostbase\EntityTransformer;
use League\Fractal\Manager;


class IpAddressController extends ResourceController
{

    /**
     * @param IpAddressRepository $ipAddresses
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(IpAddressRepository $ipAddresses, Manager $fractal, EntityTransformer $transformer)
    {
        $this->repository = $ipAddresses;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 