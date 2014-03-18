<?php namespace Hostbase\IpAddress;

use Hostbase\ResourceRepository;


/**
 * Class IpAddressUtils
 * @package Hostbase\IpAddress
 * @todo - work in progress
 */
class IpAddressUtils
{

    public static function updateIpAddressesFromHosts(array $ipFields)
    {
        $hostRepository = \App::make('HostRepository');
        $ipAddressRespository = \App::make('IpAddressRepository');

        $hosts = $hostRepository instanceof ResourceRepository ? $hostRepository->show() : null;

        if (!$hosts) {
            throw new \Exception('Unable to retrieve any hosts');
        }

        foreach ($hosts as $host) {

            $data = $hostRepository->show($host);

            foreach ($ipFields as $field) {
                if (isset($data[$field])) {
                    echo json_encode($ipAddressRespository->update($data[$field], ['host' => $data['fqdn']])) . PHP_EOL;
                }
            }
        }
    }
} 