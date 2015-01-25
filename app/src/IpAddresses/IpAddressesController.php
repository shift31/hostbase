<?php namespace Hostbase\IpAddresses;

use Hostbase\Entity\EntityTransformer;
use Hostbase\Http\ResourceController;
use League\Fractal\Manager;


class IpAddressesController extends ResourceController
{

    /**
     * @param IpAddressesService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(IpAddressesService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }
} 