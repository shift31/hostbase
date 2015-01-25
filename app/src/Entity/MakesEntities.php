<?php namespace Hostbase\Entity;


/**
 * Interface MakesEntities
 * @package Hostbase\Entity
 */
interface MakesEntities {

    /**
     * @param mixed $id
     * @param array $data
     * @return Entity
     */
    public function makeNewEntity($id = null, array $data);
} 