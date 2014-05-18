<?php namespace Hostbase\Host;

use Hostbase\Entity\Entity;
use Hostbase\Entity\EntityService;
use Hostbase\Entity\MakesEntities;


class HostService implements EntityService, MakesEntities {

    use HostMaker;

    /**
     * @var \Hostbase\Entity\EntityRepository
     */
    protected $repository;

    /**
     * @var HostFinder
     */
    protected $finder;

    /**
     * @param HostRepository $repository
     * @param HostFinder $finder
     */
    public function __construct(HostRepository $repository, HostFinder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;
    }


    /**
     * @param null|string $id
     * @return Entity
     */
    public function showOne($id)
    {
        return $this->repository->getOne($id);
    }


    /**
     * @param array $ids
     * @return array
     */
    public function showMany(array $ids)
    {
        return $this->repository->getMany($ids);
    }


    /**
     * @return array
     */
    public function showList()
    {
        return $this->search('_exists_:fqdn');
    }


    /**
     * @param $query
     * @param int $limit
     * @param bool $showData
     * @return array
     * @throws \Hostbase\Exceptions\NoSearchResults
     */
    public function search($query, $limit = 10000, $showData = false)
    {
        $docIds = $this->finder->search($query, $limit);

        if ($showData === false) {

            // set entities to an array of document IDs without the entity name prefixed
            $entities = array_map(
                function ($entity) {
                    return str_replace('host' . '_', '', $entity);
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
    public function store(Entity $entity)
    {
        return $this->repository->store($entity);
    }


    /**
     * @param Entity $entity
     *
     * @return Entity
     */
    public function update(Entity $entity)
    {
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