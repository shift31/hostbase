<?php namespace Hostbase;

use Elasticsearch\Client as ElasticsearchClient;
use Hostbase\Entity\EntityFinder;
use Hostbase\Exceptions\NoSearchResults;


abstract class ElasticsearchFinder implements EntityFinder {

    /**
     * @todo add elasticsearch index to app config
     */
    const ELASTICSEARCH_INDEX = 'hostbase';


    /**
     * @var \Elasticsearch\Client
     */
    protected $es;


    /**
     * The entity name/document type.  Used as the key prefix.
     *
     * @var string $entityName
     */
    static protected $entityName = null;


    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = null;


    /**
     * @param ElasticsearchClient $es
     * @throws \Exception
     */
    public function __construct(ElasticsearchClient $es) {
        $this->es = $es;

        if (is_null(static::$entityName) || is_null(static::$defaultSearchField)) {
            throw new \Exception("'entityName' and 'defaultSearchField' fields must not be null");
        }
    }


    /**
     * @param string $query
     * @param int    $limit
     *
     * @throws NoSearchResults
     * @return array
     */
    public function search($query, $limit = 10000)
    {
        $searchParams['index'] = self::ELASTICSEARCH_INDEX;
        $searchParams['size'] = $limit;
        $searchParams['body']['query']['query_string'] = [
            'default_field' => static::$defaultSearchField,
            'query'         => 'docType:"' . static::$entityName . '" AND ' . str_replace('/', '\\/', $query)
        ];

        $result = $this->es->search($searchParams);

        $docIds = [];

        if (is_array($result)) {
            foreach ($result['hits']['hits'] as $hit) {
                $docIds[] = $hit['_id'];
            }
        }

        if (count($docIds) === 0) {
            throw new NoSearchResults('No ' . static::$entityName . "s matching '$query' were found");
        }

        return $docIds;
    }

} 