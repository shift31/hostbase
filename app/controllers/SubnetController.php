<?php

use Hostbase\Subnet\SubnetInterface;

class SubnetController extends ResourceControllerAbstract {

	public function __construct(SubnetInterface $subnets) {
		$this->resources = $subnets;
	}
} 