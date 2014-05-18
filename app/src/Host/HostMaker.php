<?php namespace Hostbase\Host;


trait HostMaker {

    /**
     * @param string|null $fqdn
     * @param array $data
     * @return Host
     */
    public function makeNewEntity($fqdn = null, array $data = [])
    {
        return new Host($fqdn, $data);
    }
} 