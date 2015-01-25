<?php namespace Hostbase\Repository;

use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Entity\Exceptions\EntityUpdateFailed;
use Hostbase\Entity\MakesEntities;


/**
 * Class CouchbaseRepository
 * @package Hostbase\Repository
 */
abstract class CouchbaseRepository implements Repository, MakesEntities
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
     * @var string $idField
     */
    static protected $idField = null;


    /**
     * @var \CouchbaseBucket
     */
    protected $bucket;


    /**
     * @param \CouchbaseBucket $bucket
     * @throws \Exception
     */
    public function __construct(\CouchbaseBucket $bucket)
    {
        if (is_null(static::$entityName) || is_null(static::$idField)) {
            throw new \Exception("'entityName' and 'idField' fields must not be null");
        }
    }


    /**
     * @inheritdoc
     */
    public function getOne($id)
    {
        $key = $this->makeKey($id);

        $metaDoc = $this->bucket->get($key);

        if ($metaDoc === null) {
            throw new EntityNotFound('No ' . static::$entityName . " named '$id'");
        }

        return $this->makeNewEntity($this->makeIdFromKey($key), $metaDoc->value);
    }


    /**
     * @inheritdoc
     */
    public function getMany(array $ids)
    {
        $entities = [];

        $metaDocs = $this->bucket->get($ids);

        foreach ($metaDocs as $key => $metaDoc) {
            $entity = $this->makeNewEntity($this->makeIdFromKey($key), $metaDoc->value);

            $entities[] = $entity;
        }

        return $entities;
    }


    /**
     * @inheritdoc
     */
    public function store(Entity $entity)
    {
        $id = $entity->{static::$idField};
        $key = $this->makeKey($id);
        $entity->setId($key);

        // set document type and creation time
        $entity->docType = static::$entityName;
        $entity->createdDateTime = date('c');

        $this->bucket->insert($key, $entity);

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function update(Entity $entity)
    {
        $id = $entity->getId();

        $this->bucket->replace($this->makeKey($id), $entity);

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function destroy($id)
    {
        $this->bucket->remove($this->makeKey($id));
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