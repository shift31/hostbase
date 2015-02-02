<?php namespace Hostbase\Services;

use Hostbase\Entity\Entity;
use Hostbase\Entity\HandlesEntities;
use Hostbase\Finder\Finder;
use Hostbase\Repository\Repository;


/**
 * Class DefaultResourceService
 * @package Hostbase\Services
 */
abstract class DefaultResourceService implements ResourceService, HandlesEntities
{
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
     * @param int $limit
     * @param bool $showData
     * @return array
     */
    public function showList($limit = 10000, $showData = false)
    {
        return $this->search("_exists_:{$this->getEntityIdField()}", $limit, $showData);
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
                    return str_replace($this->getEntityDocType() . '_', '', $entity);
                },
                $docIds
            );
        } else {
            $entities = $this->repository->getMany($docIds);
        }

        return $entities;
    }


    /**
     * @param array $data
     * @return Entity
     */
    public function store(array $data)
    {
        $entity = $this->makeEntity($data);

        $entity->createdDateTime = date('c');

        return $this->repository->store($entity);
    }


    /**
     * @param string $id
     * @param array  $data
     *
     * @return Entity
     */
    public function update($id, array $data)
    {
        $entity = $this->repository->getOne($id);

        foreach ($data as $key => $value) {
            if ($value === '') {
                // keys should be removed when nullified
                unset($entity->$key);
            } else {
                $entity->$key = $value;
            }
        }

        $entity->updatedDateTime = date('c');

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
} 