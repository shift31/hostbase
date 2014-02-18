<?php namespace Hostbase\Host;

use Hostbase\ResourceInterface;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Cb;
use Es;
use Crypt;


class CouchbaseElasticsearchHost implements HostInterface, ResourceInterface
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
			'default_field' => 'fqdn',
			'query' => 'docType:"host" AND ' . str_replace('/', '\\/', $query)
		];

		$result = Es::search($searchParams);

		$docIds = [];

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
			$hosts = [];

			$docCollection = Cb::findByKey($docIds);

			if ($docCollection instanceof DocumentCollection) {
				foreach ($docCollection as $doc) {
					if ($doc instanceof Document) {
						$data = $doc->doc();

						/** @noinspection PhpParamsInspection */
						$this->decryptAdminPassword($data);

						$hosts[] = $data;
					}
				}
			}
		}

		//Log::debug(print_r($hosts, true));
		if (count($hosts) == 0) {
			throw new \Exception("No hosts matching '$query' were found");
		}

		return $hosts;
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
			return $this->search('_exists_:fqdn');
		} else {
			/** @noinspection PhpVoidFunctionResultUsedInspection */
			$result = Cb::findByKey("host_$fqdn", ['first' => true]);
			//Log::debug(print_r($result, true));

			if (!($result instanceof Document)) {
				throw new \Exception("No host named '$fqdn'");
			}

			$data = $result->doc();

			/** @noinspection PhpParamsInspection */
			$this->decryptAdminPassword($data);

			return $data;
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
		}

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

		// encrypt admin password
		$this->encryptAdminPassword($data);

		$fqdn = $data['fqdn'];

		$doc = [
			'key' => "host_$fqdn",
			'doc' => $data
		];

		if (!Cb::save($doc, ['override' => false])) {
			throw new \Exception("'$fqdn' already exists");
		}

		return $data;
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
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$result = Cb::findByKey("host_$fqdn", ['first' => true]);
		//Log::debug(print_r($result, true));

		if (!($result instanceof Document)) {
			throw new \Exception("No host named '$fqdn'");
		}

		// convert Document to array
		$updateData = (array) unserialize($result->serialize());

		// encrypt admin password
		$this->encryptAdminPassword($data);

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
			'key' => "host_$fqdn",
			'doc' => $updateData
		];

		//Log::debug(print_r($doc, true));

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		if (!Cb::save($doc, ['replace' => true])) {
			throw new \Exception("Unable to update '$fqdn'");
		}

		return $updateData;
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


	/**
	 * @param array $data
	 */
	public function encryptAdminPassword(array &$data)
	{
		if (isset($data['adminCredentials'])) {
			$data['adminCredentials']['encryptedPassword'] = Crypt::encrypt($data['adminCredentials']['password']);
			unset($data['adminCredentials']['password']);
		}
	}


	/**
	 * @param array $data
	 */
	public function decryptAdminPassword(array &$data)
	{
		if (isset($data['adminCredentials'])) {
			$data['adminCredentials']['password'] = Crypt::decrypt($data['adminCredentials']['encryptedPassword']);
			unset($data['adminCredentials']['encryptedPassword']);
		}
	}
}