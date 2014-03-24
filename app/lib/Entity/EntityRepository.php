<?php namespace Hostbase\Entity;


interface EntityRepository
{

    /**
     * @param string $query
     * @param int    $limit
     * @param bool   $showData
     *
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false);


    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return Entity
     */
    public function show($id = null);


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


    /**
     * @param mixed|null $id
     * @param array $data
     * @return Entity
     */
    public function makeNewEntity($id = null, array $data = []);
}