<?php namespace Hostbase\Entity;


interface MakesEntities {

    /**
     * @param mixed|null $id
     * @param array $data
     * @return Entity
     */
    public function makeNewEntity($id = null, array $data = []);
} 