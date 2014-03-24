<?php namespace Hostbase;

use Basement\data\Document;
use Basement\data\DocumentCollection;
use Cb;
use Es;
use Hostbase\Entity\Entity;
use Hostbase\Entity\EntityRepository;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Entity\Exceptions\EntityUpdateFailed;
use Hostbase\Exceptions\NoSearchResults;


abstract class CbEsRepository implements EntityRepository
{
    /**
     * @todo add elasticsearch index to app config
     */
    const ELASTICSEARCH_INDEX = 'hostbase';


    /**
     * The entity name/document type.  Used as the key prefix.
     *
     * @var string $entityName
     */
    static protected $entityName = null;

    /**
     * The data field to use for generating a new id (key suffix).
     *
     * @var string $keySuffixField
     */
    static protected $idField = null;

    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = null;


    public function __construct()
    {
        if (is_null(static::$entityName) || is_null(static::$idField) || is_null(static::$defaultSearchField)) {
            throw new \Exception("'entityName', 'idField', and 'defaultSearchField' fields must not be null");
        }
    }


    /**
     * @param string $query
     * @param int    $limit
     * @param bool   $showData
     *
     * @throws NoSearchResults
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false)
    {
        $searchParams['index'] = self::ELASTICSEARCH_INDEX;
        $searchParams['size'] = $limit;
        $searchParams['body']['query']['query_string'] = [
            'default_field' => static::$defaultSearchField,
            'query'         => 'docType:"' . static::$entityName . '" AND ' . str_replace('/', '\\/', $query)
        ];

        $result = Es::search($searchParams);

        $docIds = [];

        if (is_array($result)) {
            foreach ($result['hits']['hits'] as $hit) {
                $docIds[] = $hit['_id'];
            }
        }

        if (count($docIds) === 0) {
            throw new NoSearchResults('No ' . static::$entityName . "s matching '$query' were found");
        }

        if ($showData === false) {

            // set entities to an array of document IDs without the entity name prefixed
            $entities = array_map(
                function ($entity) {
                    return str_replace(static::$entityName . '_', '', $entity);
                },
                $docIds
            );
        } else {
            $entities = $this->getEntityCollection($docIds);
        }

        return $entities;
    }


    /**
     * @param string|null $id
     *
     * @return Entity
     */
    public function show($id = null)
    {
        // list all entities by default
        if ($id === null) {
            return $this->search('_exists_:' . static::$idField);
        }

        $doc = $this->getCbDocument($id);

        return $this->makeNewEntity($doc->key(), $doc->doc());
    }


    /**
     * @param Entity $entity
     *
     * @throws EntityAlreadyExists
     * @return Entity
     */
    public function store(Entity $entity)
    {
        $data = $entity->getData();
        $id = $data[static::$idField];
        $key = $this->makeKey($id);
        $entity->setId($key);

        // set document type and creation time
        $data['docType'] = static::$entityName;
        $data['createdDateTime'] = date('c');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if (!Cb::save($this->makeCbDocument($key, $data), ['override' => false])) {
            throw new EntityAlreadyExists("'$id' already exists");
        }

        $entity->setData($data);

        return $entity;
    }


    /**
     * @param Entity $entity
     *
     * @throws EntityUpdateFailed
     * @return Entity
     */
    public function update(Entity $entity)
    {
        $data = $entity->getData();
        $id = $data[static::$idField];

        $result = $this->getCbDocument($id);
        $key = $result->key();
        $entity->setId($key);

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

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if (!Cb::save($this->makeCbDocument($key, $updateData), ['replace' => true])) {
            throw new EntityUpdateFailed("Unable to update '$id'");
        }

        $entity->setData($updateData);

        return $entity;
    }


    /**
     * @param string $id
     *
     * @return bool
     * @throws \Exception
     */
    public function destroy($id)
    {
        // verify the entity exists by attempting to show it; an exception will be thrown if it does not exist
        $this->show($id);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        // connect to Couchbase server
        $cbConnection = Cb::connection();

        if (!$cbConnection) {
            throw new \Exception("No Couchbase connection");
        }

        $cbConnection->delete($this->makeKey($id));

        return true;
    }


    /**
     * @param mixed|null $id
     * @param array $data
     * @return Entity
     */
    abstract public function makeNewEntity($id = null, array $data = []);


    /**
     * @param array $docIds
     * @return array
     */
    protected function getEntityCollection(array $docIds)
    {
        $entities = [];

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $docCollection = Cb::findByKey($docIds);

        if ($docCollection instanceof DocumentCollection) {
            foreach ($docCollection as $doc) {
                if ($doc instanceof Document) {

                    $entity = $this->makeNewEntity($this->makeIdFromKey($doc->key()), $doc->doc());

                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }


    /**
     * @param string $key
     * @param array $doc
     * @return array
     */
    protected function makeCbDocument($key, $doc)
    {
        return [
            'key' => $key,
            'doc' => $doc
        ];
    }


    /**
     * @param string $id
     * @return Document
     * @throws EntityNotFound
     */
    protected function getCbDocument($id)
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $result = Cb::findByKey($this->makeKey($id), ['first' => true]);

        if (!($result instanceof Document)) {
            throw new EntityNotFound('No ' . static::$entityName . " named '$id'");
        }

        return $result;
    }


    /**
     * @param string $id
     * @return string
     */
    protected function makeKey($id)
    {
        return static::$entityName . "_$id";
    }


    /**
     * @param string $key
     * @return string
     */
    protected function makeIdFromKey($key)
    {
        return str_replace(static::$entityName . '_', '', $key);
    }
}