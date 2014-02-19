<?php

use Hostbase\IpAddress\IpAddressRepository;
use League\Fractal\Manager;


class IpAddressController extends ResourceController
{

    /**
     * @param IpAddressRepository $ipAddresses
     * @param Manager             $fractal
     */
    public function __construct(IpAddressRepository $ipAddresses, Manager $fractal)
    {
        $this->resources = $ipAddresses;
        $this->fractal = $fractal;
    }
} 