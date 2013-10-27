<?php

namespace Hostbase\Host;

use Basement\data\Document;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Cb;
use Es;


class CouchbaseElasticsearchHost implements HostInterface
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
		$searchParams['body']['query']['query_string']['query'] = 'docType:host AND ' . $query;

		$result = Es::search($searchParams);

		$docIds = array();

		if (is_array($result)) {
			foreach ($result['hits']['hits'] as $hit) {
				$docIds[] = $hit['_id'];
			}
		}

		if ($showData === false) {
			$hosts = array_map(
				function ($host) {
					return str_replace('host_', '', $host);
				}, $docIds
			);
		} else {
			$hosts = array();

			$docCollection = Cb::findByKey($docIds);

			if ($docCollection instanceof DocumentCollection) {
				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$hosts[] = $doc->doc();
					}
				}
			}
		}

		//Log::debug(print_r($hosts, true));
		if (count($hosts) == 0) {
			throw new \Exception("No hosts matching '$query' were found");
		} else {
			return $hosts;
		}

	}


	/**
	 * @param string|null $fqdn
	 *
	 * @throws \Exception
	 * @return array|null
	 */
	public function show($fqdn = null)
	{
		// list all hosts by default
		if ($fqdn == null) {

			$hosts = array();

			$query = new BasementQuery();
			$viewResult = Cb::findByView('hosts', 'byFqdn', $query);

			if ($viewResult instanceof ViewResult) {

				$docCollection = $viewResult->get();

				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$hosts[] = str_replace('host_', '', $doc->key());
					}
				}
			}

			//Log::debug(print_r($hosts, true));
			return $hosts;

		} else {
			$result = Cb::findByKey("host_$fqdn", array('first' => true));
			//Log::debug(print_r($result, true));

			if ($result instanceof Document) {
				return $result->doc();
			} else {
				throw new \Exception("No host named '$fqdn'");
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
		if (!isset($data['fqdn'])) {
			throw new \Exception("Host must have a value for 'fqdn'");
		} else {

			// generate hostname and domain if they don't already exist
			if (!isset($data['hostname']) && !isset($data['domain'])) {
				$fqdnParts = explode('.', $data['fqdn'], 2);
				//Log::debug('$fqdnParts:' . print_r($fqdnParts, true));
				$data['hostname'] = $fqdnParts[0];
				$data['domain'] = $fqdnParts[1];
			}

			// set document type and creation time
			$data['docType'] = 'host';
			$data['createdDateTime'] = date('c');

			$fqdn = $data['fqdn'];

			$doc = array(
				'key' => "host_$fqdn",
				'doc' => $data
			);

			if (!Cb::save($doc, array('override' => false))) {
				throw new \Exception("'$fqdn' already exists");
			} else {
				return $data;
			}
		}
	}


	/**
	 * @param string $fqdn
	 * @param array  $data
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function update($fqdn, array $data)
	{
		$result = Cb::findByKey("host_$fqdn", array('first' => true));
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
				'key' => "host_$fqdn",
				'doc' => $updateData
			);

			//Log::debug(print_r($doc, true));

			if (!Cb::save($doc, array('replace' => true))) {
				throw new \Exception("Unable to update '$fqdn'");
			} else {
				return $updateData;
			}

		} else {
			throw new \Exception("No host named '$fqdn'");
		}
	}


	/**
	 * @param string $fqdn
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	public function destroy($fqdn)
	{
		$this->show($fqdn);

		$cbConnection = Cb::connection();

		if ($cbConnection instanceof \Couchbase) {
			$cbConnection->delete("host_$fqdn");
		} else {
			throw new \Exception("No Couchbase connection");
		}
	}
}