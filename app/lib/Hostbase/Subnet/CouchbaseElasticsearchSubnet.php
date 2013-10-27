<?php

namespace Hostbase\Subnet;

use Basement\data\Document;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Cb;
use Es;


class CouchbaseElasticsearchSubnet implements SubnetInterface
{

	/**
	 * @param string $query
	 * @param int    $limit
	 * @param bool   $showData
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function search($query, $limit = 10000, $showData = false)
	{
		$searchParams['index'] = 'hostbase';
		$searchParams['size'] = $limit;
		$searchParams['body']['query']['query_string']['query'] = 'docType:subnet AND ' . $query;

		$result = Es::search($searchParams);

		$docIds = array();

		if (is_array($result)) {
			foreach ($result['hits']['hits'] as $hit) {
				$docIds[] = $hit['_id'];
			}
		}

		if ($showData === false) {
			$subnets = array_map(
				function ($subnet) {
					return str_replace('subnet_', '', $subnet);
				}, $docIds
			);
		} else {
			$subnets = array();

			$docCollection = Cb::findByKey($docIds);

			if ($docCollection instanceof DocumentCollection) {
				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$subnets[] = $doc->doc();
					}
				}
			}
		}

		//Log::debug(print_r($subnets, true));
		if (count($subnets) == 0) {
			throw new \Exception("No subnets matching '$query' were found");
		} else {
			return $subnets;
		}

	}


	/**
	 * @param string|null $subnet
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($subnet = null)
	{
		// list all subnets by default
		if ($subnet == null) {

			$subnets = array();

			$query = new BasementQuery();
			$viewResult = Cb::findByView('subnets', 'bySubnet', $query);

			if ($viewResult instanceof ViewResult) {

				$docCollection = $viewResult->get();

				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$subnets[] = str_replace('subnet_', '', $doc->key());
					}
				}
			}

			//Log::debug(print_r($subnets, true));
			return $subnets;

		} else {
			$subnet = str_replace('/', '_', $subnet);

			$result = Cb::findByKey("subnet_$subnet", array('first' => true));
			//Log::debug(print_r($result, true));

			if ($result instanceof Document) {
				return $result->doc();
			} else {
				throw new \Exception("No '$subnet' subnet");
			}
		}
	}


	/**
	 * @param array $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function store(array $data)
	{
		if (!isset($data['network']) || !isset($data['netmask']) || !isset($data['gateway']) || !isset($data['cidr'])) {
			throw new \Exception("Subnet must have a value for 'network', 'netmask', 'gateway', and 'cidr'");
		} else {

			// set document type and creation time
			$data['createdDateTime'] = date('c');
			$data['docType'] = 'subnet';

			$subnet = "{$data['network']}_{$data['cidr']}";

			$doc = array(
				'key' => "subnet_$subnet",
				'doc' => $data
			);

			if (!Cb::save($doc, array('override' => false))) {
				throw new \Exception("'$subnet' already exists");
			} else {
				return $data;
			}
		}
	}


	/**
	 * @param string $subnet
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($subnet, array $data)
	{
		$subnet = str_replace('/', '_', $subnet);

		$result = Cb::findByKey("subnet_$subnet", array('first' => true));
		//Log::debug(print_r($result, true));

		if ($result instanceof Document) {

			// convert Document to array
			$updateData = (array)unserialize($result->serialize());

			foreach ($data as $key => $value) {
				if ($value === null) {
					// keys should be removed when nullified
					unset($updateData[$key]);
				} else {
					$updateData[$key] = $value;
				}
			}

			$updateData['updatedDateTime'] = date('c');

			$doc = array(
				'key' => "subnet_$subnet",
				'doc' => $updateData
			);

			//Log::debug(print_r($doc, true));

			if (!Cb::save($doc, array('replace' => true))) {
				throw new \Exception("Unable to update '$subnet'");
			} else {
				return $updateData;
			}

		} else {
			throw new \Exception("No '$subnet' subnet");
		}
	}


	/**
	 * @param string $subnet
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($subnet)
	{
		$subnet = str_replace('/', '_', $subnet);

		$this->show($subnet);

		$cbConnection = Cb::connection();

		if ($cbConnection instanceof \Couchbase) {
			$cbConnection->delete("subnet_$subnet");
		} else {
			throw new \Exception("No Couchbase connection");
		}
	}
}