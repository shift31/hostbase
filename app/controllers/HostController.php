<?php

use Hostbase\Host\HostInterface;

class HostController extends ResourceController {

	public function __construct(HostInterface $hosts) {
		$this->resources = $hosts;
	}
} 