<?php

namespace Hostbase;


interface ResourceInterface
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
	 * @param string|null $id
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($id = null);


	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function store(array $data);


	/**
	 * @param string $id
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($id, array $data);


	/**
	 * @param string $id
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($id);
}