<?php namespace Hostbase\Hosts;


trait HostMaker {

    /**
     * @param string $fqdn
     * @param array $data
     * @return Host
     */
    public function makeNewEntity($fqdn = null, array $data)
    {
        return new Host($fqdn, $data);
    }
} 