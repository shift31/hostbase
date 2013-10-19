<?php

namespace Hostbase;

use Log;
use Cb;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Monolog\Logger;
use Elasticsearch\Client as EsClient;


class HostImpl implements HostInterface
{

	/**
	 * @param string $query
	 * @param bool   $showData
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function search($query, $showData = false)
	{
		//TODO: make elasticsearch service provider

		$connParams = array();
		$connParams['hosts'] = array(
			'localhost:9200'
		);
		$connParams['logPath'] = storage_path() . '/logs/hostbase-elasticsearch' . php_sapi_name() . '.log';
		$connParams['logLevel'] = Logger::INFO;


		$client = new EsClient($connParams);


		$searchParams['index'] = 'hostbase';
		$searchParams['body']['query']['query_string']['query'] = $query;

		$result = $client->search($searchParams);

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

			/** @noinspection PhpVoidFunctionResultUsedInspection */
			$docCollection = Cb::findByKey($docIds);

			if ($docCollection instanceof DocumentCollection) {
				foreach ($docCollection as $doc) {
					/** @noinspection PhpUndefinedMethodInspection */
					$hosts[] = $doc->doc();
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
					/** @noinspection PhpUndefinedMethodInspection */
					$hosts[] = str_replace('host_', '', $doc->key());
				}
			}

			//Log::debug(print_r($hosts, true));
			return $hosts;

		} else {

			/** @noinspection PhpVoidFunctionResultUsedInspection */
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

			$fqdn = $data['fqdn'];

			$doc = array(
				'key' => "host_$fqdn",
				'doc' => $data
			);

			/** @noinspection PhpVoidFunctionResultUsedInspection */
			if (!Cb::save($doc, array('override' => false))) {
				throw new \Exception("'$fqdn' already exists");
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
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$result = Cb::findByKey("host_$fqdn", array('first' => true));
		//Log::debug(print_r($result, true));

		if ($result instanceof Document) {

			// convert Document to array
			$updateData = (array)unserialize($result->serialize());

			foreach ($data as $key => $value) {
				if ($value == null) {
					// keys should be removed when nullified
					unset($updateData[$key]);
				} else {
					$updateData[$key] = $value;
				}
			}

			$doc = array(
				'key' => "host_$fqdn",
				'doc' => $updateData
			);

			//Log::debug(print_r($doc, true));

			/** @noinspection PhpVoidFunctionResultUsedInspection */
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
	 * @return mixed
	 */
	public function destroy($fqdn)
	{
		$this->show($fqdn);
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		/** @noinspection PhpUndefinedMethodInspection */
		Cb::connection()->delete("host_$fqdn");
	}
}