<?php namespace Hostbase\IpAddress;


interface IpAddressInterface
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
	 * @param string|null $ipAddress
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($ipAddress = null);


	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function store(array $data);


	/**
	 * @param string $ipAddress
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($ipAddress, array $data);


	/**
	 * @param string $ipAddress
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($ipAddress);
}