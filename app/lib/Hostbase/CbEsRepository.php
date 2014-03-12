<?php namespace Hostbase;

use Basement\data\Document;
use Basement\data\DocumentCollection;
use Cb;
use Es;
use Hostbase\Exceptions\NoSearchResultsException;
use Hostbase\Exceptions\ResourceNotFoundException;


abstract class CbEsRepository implements ResourceRepository
{
    /**
     * @todo add elasticsearch index to app config
     */
    const ELASTICSEARCH_INDEX = 'hostbase';


    /**
     * The resource name/document type.  Used as the key prefix.
     *
     * @var string $resourceName
     */
    static protected $resourceName = null;

    /**
     * The document field to use as the key suffix.
     *
     * @var string $keySuffixField
     */
    static protected $keySuffixField = null;

    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = null;


    public function __construct()
    {
        if (is_null(static::$resourceName) || is_null(static::$keySuffixField) || is_null(static::$defaultSearchField)) {
            throw new \Exception("'resourceName', 'keySuffixField', and 'defaultSearchField' fields must not be null");
        }
    }


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
        $searchParams['index'] = self::ELASTICSEARCH_INDEX;
        $searchParams['size'] = $limit;
        $searchParams['body']['query']['query_string'] = [
            'default_field' => static::$defaultSearchField,
            'query'         => 'docType:"' . static::$resourceName . '" AND ' . str_replace('/', '\\/', $query)
        ];

        $result = Es::search($searchParams);

        $docIds = [];

        if (is_array($result)) {
            foreach ($result['hits']['hits'] as $hit) {
                $docIds[] = $hit['_id'];
            }
        }

        if (count($docIds) === 0) {
            throw new NoSearchResultsException('No ' . static::$resourceName . "s matching '$query' were found");
        }

        if ($showData === false) {
            $resources = array_map(
                function ($resource) {
                    return str_replace(static::$resourceName . '_', '', $resource);
                },
                $docIds
            );
        } else {
            $resources = [];

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            $docCollection = Cb::findByKey($docIds);

            if ($docCollection instanceof DocumentCollection) {
                foreach ($docCollection as $doc) {
                    if ($doc instanceof Document) {
                        $resources[] = $doc->doc();
                    }
                }
            }
        }

        return $resources;
    }


    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return array|null
     */
    public function show($id = null)
    {
        // list all resources by default
        if ($id == null) {
            return $this->search('_exists_:' . static::$keySuffixField);
        } else {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            $result = Cb::findByKey(static::$resourceName . "_$id", ['first' => true]);
            //Log::debug(print_r($result, true));

            if (!($result instanceof Document)) {
                throw new ResourceNotFoundException('No ' . static::$resourceName . " named '$id'");
            }

            return $result->doc();
        }
    }


    /**
     * @param array $data
     *
     * @param null|string  $id
     *
     * @throws \Exception
     * @return mixed
     */
    public function store(array $data, $id = null)
    {

        // set document type and creation time
        $data['docType'] = static::$resourceName;
        $data['createdDateTime'] = date('c');

        $key = $id ?: $data[static::$keySuffixField];

        $doc = [
            'key' => static::$resourceName . "_$key",
            'doc' => $data
        ];

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if (!Cb::save($doc, ['override' => false])) {
            throw new \Exception("'$key' already exists");
        }

        return $data;
    }


    /**
     * @param string $id
     * @param array  $data
     *
     * @throws \Exception
     * @return mixed
     */
    public function update($id, array $data)
    {
        $key = static::$resourceName . "_$id";

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $result = Cb::findByKey($key, ['first' => true]);
        //Log::debug(print_r($result, true));

        if (!($result instanceof Document)) {
            throw new ResourceNotFoundException('No ' . static::$resourceName . " named '$id'");
        }

        // convert Document to array
        $updateData = (array) unserialize($result->serialize());

        foreach ($data as $field => $value) {
            if ($value === null) {
                // keys should be removed when nullified
                unset($updateData[$field]);
            } else {
                $updateData[$field] = $value;
            }
        }

        $updateData['updatedDateTime'] = date('c');

        $doc = [
            'key' => $key,
            'doc' => $updateData
        ];

        //Log::debug(print_r($doc, true));

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if (!Cb::save($doc, ['replace' => true])) {
            throw new \Exception("Unable to update '$id'");
        }

        return $updateData;
    }


    /**
     * @param string $id
     *
     * @throws \Exception
     * @return mixed
     */
    public function destroy($id)
    {
        $this->show($id);

        $cbConnection = Cb::connection();

        if ($cbConnection instanceof \Couchbase) {
            $cbConnection->delete(static::$resourceName . "_$id");
        } else {
            throw new \Exception("No Couchbase connection");
        }
    }
}