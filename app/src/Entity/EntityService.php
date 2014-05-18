<?php namespace Hostbase\Entity;


interface EntityService
{
    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return Entity
     */
    public function showOne($id);


    /**
     * @param array $ids
     * @return array
     */
    public function showMany(array $ids);


    /**
     * @return array
     */
    public function showList();


    /**
     * @param $query
     * @param int $limit
     * @param bool $showData
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false);


    /**
     * @param Entity $entity
     *
     * @return Entity
     */
    public function store(Entity $entity);


    /**
     * @param Entity $entity
     *
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