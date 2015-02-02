<?php namespace Hostbase\IpAddresses;

use Hostbase\Hosts\Repository\HostRepository;
use Hostbase\IpAddresses\Repository\IpAddressRepository;


/**
 * Class IpAddressUtils
 * @package Hostbase\IpAddresses
 * @todo - work in progress
 */
class IpAddressUtils
{

    public static function updateIpAddressesFromHosts(array $ipFields)
    {
        /** @var HostRepository $hostsRepository */
        $hostsRepository = \App::make(HostRepository::class);

        /** @var IpAddressRepository $ipAddressRespository */
        $ipAddressRespository = \App::make(IpAddressRepository::class);

        $hosts = $hostsRepository->show();

        if (!$hosts) {
            throw new \Exception('Unable to retrieve any hosts');
        }

        foreach ($hosts as $host) {

            $data = $hostsRepository->show($host);

            foreach ($ipFields as $field) {
                if (isset($data[$field])) {
                    echo json_encode($ipAddressRespository->update($data[$field], ['host' => $data['fqdn']])) . PHP_EOL;
                }
            }
        }
    }
} 