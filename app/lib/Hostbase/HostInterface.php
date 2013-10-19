<?php

namespace Hostbase;


interface HostInterface
{

	/**
	 * @param string $query
	 * @param bool   $showData
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function search($query, $showData = false);


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
}