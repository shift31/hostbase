<?php namespace Hostbase\IpAddresses;


trait IpAddressMaker {

    /**
     * @param string|null $ipAddress
     * @param array $data
     * @return IpAddress
     */
    public function makeNewEntity($ipAddress = null, array $data = [])
    {
        return new IpAddress($ipAddress, $data);
    }
} 