<?php namespace Hostbase\Entity;


interface Entity
{
    /**
     * @param mixed $id
     * @param array $data
     */
    public function __construct($id = null, array $data = []);


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
    public function getData();


    /**
     * @param array $data
     */
    public function setData(array $data);
} 