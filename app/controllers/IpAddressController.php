<?php

use Hostbase\IpAddress\IpAddressInterface;

class IpAddressController extends ResourceController {

	public function __construct(IpAddressInterface $ipAddresses) {
		$this->resources = $ipAddresses;
	}
} 