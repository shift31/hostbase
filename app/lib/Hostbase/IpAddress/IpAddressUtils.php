<?php namespace Hostbase\IpAddress;

use Hostbase\Host\CbEsHostRepository as Host;
use Hostbase\IpAddress\CbEsIpAddressRepository as IpAddress;

class IpAddressUtils
{

    public static function updateIpAddressesFromHosts(array $ipFields)
    {
        $hostMan = new Host();
        $ipAddressMan = new IpAddress();

        $hosts = $hostMan->show();

        foreach ($hosts as $host) {

            $data = $hostMan->show($host);

            foreach ($ipFields as $field) {
                if (isset($data[$field])) {
                    echo json_encode($ipAddressMan->update($data[$field], ['host' => $data['fqdn']])) . PHP_EOL;
                }
            }
        }
    }
} 