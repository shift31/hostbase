<?php namespace Hostbase\Entity;


interface EntityRepository
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
     * @throws \Hostbase\Entity\Exceptions\EntityAlreadyExists
     * @return Entity
     */
    public function store(Entity $entity);


    /**
     * @param Entity $entity
     *
     * @throws \Hostbase\Entity\Exceptions\EntityUpdateFailed
     * @return Entity
     */
    public function update(Entity $entity);


    /**
     * @param string $id
     *
     * @throws \Exception
     * @return bool
     */
    public function destroy($id);
}