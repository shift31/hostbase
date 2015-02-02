<?php namespace Hostbase\Services;

use Hostbase\Entity\Entity;


/**
 * Interface ResourceService
 * @package Hostbase\Services
 */
interface ResourceService
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
     * @param int $limit
     * @param bool $showData
     * @return array
     */
    public function showList($limit = 10000, $showData = false);


    /**
     * @param $query
     * @param int $limit
     * @param bool $showData
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false);


    /**
     * @param array $data
     *
     * @return Entity
     */
    public function store(array $data);


    /**
     * @param string $id
     * @param array $data
     *
     * @return Entity
     */
    public function update($id, array $data);


    /**
     * @param string $id
     *
     * @throws \Exception
     * @return bool
     */
    public function destroy($id);
}