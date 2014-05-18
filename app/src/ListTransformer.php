<?php namespace Hostbase;

use League\Fractal\TransformerAbstract;


class ListTransformer extends TransformerAbstract {

    public function transform($list)
    {
        return $list;
    }
} 