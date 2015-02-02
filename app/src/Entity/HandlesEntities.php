<?php namespace Hostbase\Entity;


/**
 * Interface HandlesEntities
 * @package Hostbase\Entity
 */
interface HandlesEntities {

    /**
     * @param array $data
     * @return Entity
     */
    public function makeEntity(array $data);


    /**
     * @return string
     */
    public function getEntityIdField();


    /**
     * @return string
     */
    public function getEntityDocType();
} 