<?php namespace Hostbase\IpAddress;

use Hostbase\ResourceController;
use Hostbase\Entity\EntityTransformer;
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
        $this->service = $ipAddresses;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 