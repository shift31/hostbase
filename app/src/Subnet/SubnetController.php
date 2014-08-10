<?php namespace Hostbase\Subnet;

use Hostbase\Resource\ResourceController;
use Hostbase\Entity\EntityTransformer;
use League\Fractal\Manager;


class SubnetController extends ResourceController
{

    /**
     * @param SubnetService $service
     * @param Manager $fractal
     * @param EntityTransformer $transformer
     */
    public function __construct(SubnetService $service, Manager $fractal, EntityTransformer $transformer)
    {
        $this->service = $service;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    public function show($id)
    {
        $this->restoreCidrNotation($id);

        return parent::show($id);
    }


    public function update($id)
    {
        $this->restoreCidrNotation($id);

        return parent::update($id);
    }


    public function destroy($id)
    {
        $this->restoreCidrNotation($id);

        return parent::destroy($id);
    }


    /**
     * @param $id
     */
    protected function restoreCidrNotation(&$id)
    {
        $id = str_replace('_', '/', $id);
    }
} 