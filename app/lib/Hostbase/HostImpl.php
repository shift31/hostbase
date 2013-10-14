<?php

namespace Hostbase;

use Log;
use Cb;
use Basement\data\DocumentCollection;
use Basement\view\Query as BasementQuery;
use Basement\view\ViewResult;
use Elasticsearch\Client as EsClient;


class HostImpl implements HostInterface {

    /**
     * @param string $query
     * @param bool $showData
     * @return array
     */
    public function search($query, $showData = false)
    {
        $connParams = array();
        $connParams['hosts'] = array (
            'localhost:9200'
        );
        $connParams['logPath']  = storage_path().'/logs/elasticsearch.log';
        $connParams['logLevel'] = \Monolog\Logger::INFO;


        $client = new EsClient($connParams);


        $searchParams['index'] = 'hostbase';
        $searchParams['body']['query']['query_string']['query'] = $query;

        $result = $client->search($searchParams);

        if (is_array($result)) {
            $docIds = array();

            foreach ($result['hits']['hits'] as $hit) {
                $docIds[] = $hit['_id'];
            }
        }

        if ($showData === false) {
             $hosts = array_map(function($host) {
                 return str_replace('host_', '', $host);
             }, $docIds);
        } else {
            $hosts = array();

            $docCollection = Cb::findByKey($docIds);

            if ($docCollection instanceof DocumentCollection) {
                foreach ($docCollection as $doc) {
                    $hosts[] = $doc->doc();
                }
            }
        }

        //Log::debug(print_r($hosts, true));
        return $hosts;
    }


    /**
     * @param string|null $fqdn
     * @return array
     */
    public function get($fqdn = null)
    {

        $hosts = array();

        // list all hosts by default
        if ($fqdn == null) {

            $query = new BasementQuery();
            $viewResult = Cb::findByView('hosts', 'byFqdn', $query);

            if ($viewResult instanceof ViewResult) {

                $docCollection = $viewResult->get();

                foreach ($docCollection as $doc) {
                    $hosts[] = str_replace('host_', '', $doc->key());
                }
            }

        }

        //Log::debug(print_r($hosts, true));
        return $hosts;
    }

    /**
     * @param string $fqdn
     * @param array $data
     * @return mixed
     */
    public function add($fqdn, array $data)
    {

    }

    /**
     * @param string $fqdn
     * @param array $data
     * @return mixed
     */
    public function modify($fqdn, array $data)
    {

    }


    /**
     * @param string $fqdn
     * @return mixed
     */
    public function remove($fqdn)
    {

    }
}