<?php namespace Hostbase\Repository;

use CouchbaseBucket;
use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Entity\HandlesEntities;


/**
 * Class CouchbaseRepository
 * @package Hostbase\Repository
 */
abstract class CouchbaseRepository implements Repository, HandlesEntities
{
    /**
     * @var CouchbaseBucket
     */
    protected $bucket;


    /**
     * @param CouchbaseBucket $bucket
     * @throws \Exception
     */
    public function __construct(CouchbaseBucket $bucket)
    {
        $this->bucket = $bucket;
    }


    /**
     * @inheritdoc
     */
    public function getOne($id)
    {
        $key = $this->makeKey($id);

        try {
            $metaDoc = $this->bucket->get($key);
        } catch (\Exception $e) {
            throw new EntityNotFound("No '{$this->getEntityDocType()}' named '$id'");
        }

        // convert object to array
        $data = json_decode(json_encode($metaDoc->value), true);

        return $this->makeEntity($data);
    }


    /**
     * @inheritdoc
     */
    public function getMany(array $ids)
    {
        $entities = [];

        $metaDocs = $this->bucket->get($ids);

        foreach ($metaDocs as $key => $metaDoc) {
            // convert object to array
            $data = json_decode(json_encode($metaDoc->value), true);

            $entity = $this->makeEntity($data);

            $entities[] = $entity;
        }

        return $entities;
    }


    /**
     * @inheritdoc
     */
    public function store(Entity $entity)
    {
        $id = $entity->getId();
        $key = $this->makeKey($id);

        try {
            $this->bucket->insert($key, $entity->toArray());
        } catch (\Exception $e) {
            throw new EntityAlreadyExists("A '{$entity->getDocType()}' with the '{$entity::getIdField()}' '$id' already exists");
        }

        return $entity;
    }


    /**
     * @inheritdoc
     */
    public function update(Entity $entity)
    {
        $id = $entity->getId();

        $this->bucket->replace($this->makeKey($id), $entity->toArray());

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
        return $this->getEntityDocType() . "_$id";
    }
}