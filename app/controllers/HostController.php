<?php

use Hostbase\Host\HostInterface;
use League\Fractal\Manager;


class HostController extends ResourceControllerAbstract {

	/**
	 * @param HostInterface $hosts
	 * @param Manager       $fractal
	 */
	public function __construct(HostInterface $hosts, Manager $fractal) {
		$this->resources = $hosts;
		$this->fractal = $fractal;
	}
} 