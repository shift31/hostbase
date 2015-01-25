<?php namespace Hostbase\Entity;


/**
 * Interface Entity
 * @package Hostbase\Entity
 */
interface Entity
{
    /**
     * @param mixed $id
     * @param array $data
     */
    public function __construct($id = null, array $data);


    /**
     * @return mixed
     */
    public function getId();


    /**
     * @param mixed $id
     */
    public function setId($id);


    /**
     * @return array
     */
    public function toArray();
} 