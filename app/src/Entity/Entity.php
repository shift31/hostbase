<?php namespace Hostbase\Entity;


/**
 * Interface Entity
 * @package Hostbase\Entity
 */
interface Entity
{
    /**
     * @param string $id
     * @param array $data
     */
    public function __construct($id = null, array $data);


    /**
     * @return string
     */
    public static function getIdField();


    /**
     * @return string
     */
    public static function getDocType();


    /**
     * @return string
     */
    public function getId();


    /**
     * @return array
     */
    public function toArray();
} 