<?php

use Hostbase\IpAddress\IpAddressInterface;
use League\Fractal\Manager;


class IpAddressController extends ResourceControllerAbstract {

	/**
	 * @param IpAddressInterface $ipAddresses
	 * @param Manager            $fractal
	 */
	public function __construct(IpAddressInterface $ipAddresses, Manager $fractal) {
		$this->resources = $ipAddresses;
		$this->fractal = $fractal;
	}
} 