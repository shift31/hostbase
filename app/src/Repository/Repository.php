<?php namespace Hostbase\Repository;

use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityUpdateFailed;


interface Repository
{
    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return Entity
     */
    public function getOne($id);


    /**
     * @param array $ids
     * @return array
     */
    public function getMany(array $ids);


    /**
     * @param Entity $entity
     *
     * @throws EntityAlreadyExists
     * @return Entity
     */
    public function store(Entity $entity);


    /**
     * @param Entity $entity
     *
     * @throws EntityUpdateFailed
     * @return Entity
     */
    public function update(Entity $entity);


    /**
     * @param string $id
     */
    public function destroy($id);
}