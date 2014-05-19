<?php namespace Hostbase\Entity;


abstract class BaseEntityService implements EntityService, MakesEntities
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
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityFinder
     */
    protected $finder;


    public function __construct(EntityRepository $repository, EntityFinder $finder)
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
     * @param Entity $entity
     *
     * @return Entity
     */
    abstract public function update(Entity $entity);


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