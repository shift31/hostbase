<?php namespace Hostbase\IpAddress;

use Hostbase\ResourceInterface;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Cb;
use Es;
use Validator;


class CouchbaseElasticsearchIpAddress implements IpAddressInterface, ResourceInterface
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
		$searchParams['body']['query']['query_string'] = array(
			'default_field' => 'ipAddress',
			'query' => 'docType:"ipAddress" AND ' . $query
		);

		$result = Es::search($searchParams);

		$docIds = array();

		if (is_array($result)) {
			foreach ($result['hits']['hits'] as $hit) {
				$docIds[] = $hit['_id'];
			}
		}

		if ($showData === false) {
			$ipAddresses = array_map(
				function ($ipAddress) {
					return str_replace('ipAddress_', '', $ipAddress);
				}, $docIds
			);
		} else {
			$ipAddresses = array();

			$docCollection = Cb::findByKey($docIds);

			if ($docCollection instanceof DocumentCollection) {
				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$ipAddresses[] = $doc->doc();
					}
				}
			}
		}

		//Log::debug(print_r($ipAddresses, true));
		if (count($ipAddresses) == 0) {
			throw new \Exception("No IP addresses matching '$query' were found");
		}

		return $ipAddresses;
	}


	/**
	 * @param string|null $ipAddress
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($ipAddress = null)
	{
		// list all IP addresses by default
		if ($ipAddress == null) {

			$ipAddresses = array();

			$query = new BasementQuery();
			$viewResult = Cb::findByView('ipAddresses', 'byIpAddress', $query);

			if ($viewResult instanceof ViewResult) {

				$docCollection = $viewResult->get();

				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$ipAddresses[] = str_replace('ipAddress_', '', $doc->key());
					}
				}
			}

			//Log::debug(print_r($ipAddresses, true));
			return $ipAddresses;

		} else {
			$result = Cb::findByKey("ipAddress_$ipAddress", array('first' => true));
			//Log::debug(print_r($result, true));

			if (!($result instanceof Document)) {
				throw new \Exception("No IP address '$ipAddress' exists");
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
			array(
			     'subnet'       => 'required',
			     'ipAddress'    => 'required'
			)
		);

		if ($validator->fails()) {
			throw new \Exception(join('; ', $validator->messages()->all()));
		}

		// set document type and creation time
		$data['createdDateTime'] = date('c');
		$data['docType'] = 'ipAddress';

		$ipAddress = $data['ipAddress'];

		$doc = array(
			'key' => "ipAddress_$ipAddress",
			'doc' => $data
		);

		if (!Cb::save($doc, array('override' => false))) {
			throw new \Exception("'$ipAddress' already exists");
		}

		return $data;
	}


	/**
	 * @param string $ipAddress
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($ipAddress, array $data)
	{
		$result = Cb::findByKey("ipAddress_$ipAddress", array('first' => true));
		//Log::debug(print_r($result, true));

		if (!($result instanceof Document)) {
			throw new \Exception("No IP address '$ipAddress' exists");
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

		$doc = array(
			'key' => "ipAddress_$ipAddress",
			'doc' => $updateData
		);

		//Log::debug(print_r($doc, true));

		if (!Cb::save($doc, array('replace' => true))) {
			throw new \Exception("Unable to update '$ipAddress'");
		}

		return $updateData;
	}


	/**
	 * @param string $ipAddress
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($ipAddress)
	{
		$this->show($ipAddress);

		$cbConnection = Cb::connection();

		if ($cbConnection instanceof \Couchbase) {
			$cbConnection->delete("ipAddress_$ipAddress");
		} else {
			throw new \Exception("No Couchbase connection");
		}
	}
}