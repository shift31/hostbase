<?php

namespace Hostbase\Host;


interface HostInterface
{

	/**
	 * @param string $query
	 * @param int    $limit
	 * @param bool   $showData
	 *
	 * @return array
	 */
	public function search($query, $limit = 10000, $showData = false);


	/**
	 * @param string|null $fqdn
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($fqdn = null);


	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function store(array $data);


	/**
	 * @param string $fqdn
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($fqdn, array $data);


	/**
	 * @param string $fqdn
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($fqdn);


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