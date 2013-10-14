<?php

namespace Hostbase;

use Log;
use Cb;
use Basement\data\Document;
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

        $docIds = array();

        if (is_array($result)) {
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
        return $hosts;
    }


    /**
     * @param string|null $fqdn
     * @return array|null
     */
    public function get($fqdn = null)
    {
        // list all hosts by default
        if ($fqdn == null) {

            $hosts = array();

            $query = new BasementQuery();
            $viewResult = Cb::findByView('hosts', 'byFqdn', $query);

            if ($viewResult instanceof ViewResult) {

                $docCollection = $viewResult->get();

                foreach ($docCollection as $doc) {
                    $hosts[] = str_replace('host_', '', $doc->key());
                }
            }

            //Log::debug(print_r($hosts, true));
            return $hosts;

        } else {

            $result = Cb::findByKey("host_$fqdn", array('first' => true));
            Log::debug(print_r($result, true));

            if ($result instanceof Document) {
                return $result->doc();
            } else {
                return null;
            }
        }
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