<?php namespace Hostbase\Host;


interface HostInterface
{

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function encryptAdminPassword(array &$data);


	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function decryptAdminPassword(array &$data);
}