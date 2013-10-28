<?php

use Hostbase\Host\HostInterface;

class HostController extends ResourceControllerAbstract {

	public function __construct(HostInterface $hosts) {
		$this->resources = $hosts;
	}
} 