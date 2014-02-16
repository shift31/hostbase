<?php namespace Hostbase\Subnet;

use Hostbase\ResourceInterface;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Cb;
use Es;
use Validator;


class CouchbaseElasticsearchSubnet implements SubnetInterface, ResourceInterface
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
		$searchParams['body']['query']['query_string'] = [
			'default_field' => 'network',
		    'query' => 'docType:"subnet" AND ' . str_replace('/', '\\/', $query)
		];

		$result = Es::search($searchParams);

		$docIds = [];

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
			$subnets = [];

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
		}

		return $subnets;
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

			$subnets = [];

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

			$result = Cb::findByKey("subnet_$subnet", ['first' => true]);
			//Log::debug(print_r($result, true));

			if (!($result instanceof Document)) {
				throw new \Exception("No '$subnet' subnet");

			}

			return $result->doc();
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

		$validator = Validator::make(
			$data,
			[
			     'network'  => 'required',
			     'netmask'  => 'required',
			     'gateway'  => 'required',
			     'cidr'     => 'required'
			]
		);

		if ($validator->fails()) {
			throw new \Exception(join('; ', $validator->messages()->all()));
		}

		// set document type and creation time
		$data['createdDateTime'] = date('c');
		$data['docType'] = 'subnet';

		$subnet = "{$data['network']}_{$data['cidr']}";

		$doc = [
			'key' => "subnet_$subnet",
			'doc' => $data
		];

		if (!Cb::save($doc, ['override' => false])) {
			throw new \Exception("'$subnet' already exists");
		}

		return $data;
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
		$result = Cb::findByKey("subnet_$subnet", ['first' => true]);
		//Log::debug(print_r($result, true));

		if (!($result instanceof Document)) {
			throw new \Exception("No '$subnet' subnet");
		}

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

		$doc = [
			'key' => "subnet_$subnet",
			'doc' => $updateData
		];

		//Log::debug(print_r($doc, true));

		if (!Cb::save($doc, ['replace' => true])) {
			throw new \Exception("Unable to update '$subnet'");
		}

		return $updateData;
	}


	/**
	 * @param string $subnet
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($subnet)
	{
		$this->show($subnet);

		$cbConnection = Cb::connection();

		if ($cbConnection instanceof \Couchbase) {
			$cbConnection->delete("subnet_$subnet");
		} else {
			throw new \Exception("No Couchbase connection");
		}
	}
}