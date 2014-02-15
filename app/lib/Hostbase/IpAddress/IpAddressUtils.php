<?php namespace Hostbase\IpAddress;

use Hostbase\Host\CouchbaseElasticsearchHost as Host;
use Hostbase\IpAddress\CouchbaseElasticsearchIpAddress as IpAddress;

class IpAddressUtils {

	public static function updateIpAddressesFromHosts(array $ipFields) {
		$hostMan = new Host();
		$ipAddressMan = new IpAddress();

		$hosts = $hostMan->show();

		foreach ($hosts as $host) {

			$data = $hostMan->show($host);

			foreach ($ipFields as $field) {
				if (isset($data[$field])) {
					echo json_encode($ipAddressMan->update($data[$field], array('host' => $data['fqdn']))) . PHP_EOL;
				}
			}
		}
	}
} 