<?php namespace Hostbase\Subnet;


interface SubnetInterface
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
	 * @param string|null $subnet
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($subnet = null);


	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function store(array $data);


	/**
	 * @param string $subnet
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($subnet, array $data);


	/**
	 * @param string $subnet
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($subnet);
}