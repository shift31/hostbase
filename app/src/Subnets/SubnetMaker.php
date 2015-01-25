<?php namespace Hostbase\Subnets;


trait SubnetMaker {

    /**
     * @param string|null $network
     * @param array $data
     * @return Subnet
     */
    public function makeNewEntity($network = null, array $data = [])
    {
        return new Subnet($network, $data);
    }
} 