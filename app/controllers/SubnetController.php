<?php

use Hostbase\Subnet\SubnetInterface;

class SubnetController extends ResourceController {

	public function __construct(SubnetInterface $subnets) {
		$this->resources = $subnets;
	}
} 