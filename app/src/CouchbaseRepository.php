<?php namespace Hostbase;

use Basement\Client;
use Basement\data\Document;
use Basement\data\DocumentCollection;
use Hostbase\Entity\Entity;
use Hostbase\Entity\EntityRepository;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Entity\Exceptions\EntityUpdateFailed;
use Hostbase\Entity\MakesEntities;


abstract class CouchbaseRepository implements EntityRepository, MakesEntities
{
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
     * @var Client
     */
    protected $cb;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->cb = \App::make('basement');

        if (is_null(static::$entityName) || is_null(static::$idField)) {
            throw new \Exception("'entityName' and 'idField' fields must not be null");
        }
    }


    /**
     * @param string|null $id
     *
     * @return Entity
     */
    public function getOne($id)
    {
        $doc = $this->getCbDocument($id);

        return $this->makeNewEntity($doc->key(), $doc->doc());
    }


    /**
     * @param array $ids
     * @return array
     */
    public function getMany(array $ids)
    {
        $entities = [];

        $docCollection = $this->cb->findByKey($ids);

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
        if (! $this->cb->save($this->makeCbDocument($key, $data), ['override' => false])) {
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

        $doc = $this->getCbDocument($id);
        $key = $doc->key();
        $entity->setId($key);

        // convert Document to array
        $updateData = (array) unserialize($doc->serialize());

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
        if (! $this->cb->save($this->makeCbDocument($key, $updateData), ['replace' => true])) {
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
        $this->getOne($id);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        // connect to Couchbase server
        $cbConnection = $this->cb->connection();

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
        $result = $this->cb->findByKey($this->makeKey($id), ['first' => true]);

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