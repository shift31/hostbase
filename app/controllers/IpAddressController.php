<?php

use Hostbase\IpAddress\IpAddressInterface;

class IpAddressController extends ResourceControllerAbstract {

	public function __construct(IpAddressInterface $ipAddresses) {
		$this->resources = $ipAddresses;
	}
} 