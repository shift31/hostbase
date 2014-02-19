<?php namespace Hostbase;

use League\Fractal\TransformerAbstract;

class PassThruResourceTransformer extends TransformerAbstract
{

    public function transform($resource)
    {
        return $resource;
    }
} 