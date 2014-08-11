<?php namespace Hostbase\Resource;

use Hostbase\Entity\Entity;
use Hostbase\Entity\MakesEntities;
use Hostbase\Finder\Finder;
use Hostbase\Repository\Repository;


abstract class BaseResourceService implements ResourceService, MakesEntities
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
     * @var Repository
     */
    protected $repository;

    /**
     * @var Finder
     */
    protected $finder;


    public function __construct(Repository $repository, Finder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;

        if (is_null(static::$entityName) || is_null(static::$idField)) {
            throw new \Exception("'entityName' and 'idField' fields must not be null");
        }
    }

    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return Entity
     */
    public function showOne($id)
    {
        $entity = $this->repository->getOne($id);

        return $entity;
    }


    /**
     * @param array $ids
     * @return array
     */
    public function showMany(array $ids)
    {
        $entities = $this->repository->getMany($ids);

        return $entities;
    }


    /**
     * @return array
     */
    public function showList()
    {
        return $this->search('_exists_:' . static::$idField);
    }


    /**
     * @param $query
     * @param int $limit
     * @param bool $showData
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false)
    {
        $docIds = $this->finder->search($query, $limit);

        if ($showData === false) {

            // set entities to an array of document IDs without the entity name prefixed
            $entities = array_map(
                function ($entity) {
                    return str_replace(static::$entityName . '_', '', $entity);
                },
                $docIds
            );
        } else {
            $entities = $this->repository->getMany($docIds);
        }

        return $entities;
    }


    /**
     * @param Entity $entity
     *
     * @return Entity
     */
    abstract public function store(Entity $entity);


    /**
     * @param string $id
     * @param array  $data
     *
     * @return Entity
     */
    public function update($id, array $data)
    {
        $entity = $this->repository->getOne($id);

        $existingData = $entity->getData();

        foreach ($data as $field => $value) {
            if ($value === '') {
                // keys should be removed when nullified
                unset($existingData[$field]);
            } else {
                $existingData[$field] = $value;
            }
        }

        $existingData['updatedDateTime'] = date('c');

        $entity->setData($existingData);

        return $this->repository->update($entity);
    }


    /**
     * @param string $id
     *
     * @throws \Exception
     * @return bool
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }


    /**
     * @param mixed|null $id
     * @param array $data
     * @return Entity
     */
    abstract public function makeNewEntity($id = null, array $data = []);
} 