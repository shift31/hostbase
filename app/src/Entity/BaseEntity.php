<?php namespace Hostbase\Entity;


/**
 * Class BaseEntity
 * @package Hostbase\Entity
 */
abstract class BaseEntity implements Entity
{
    /**
     * @var mixed
     */
    public $id;

    /**
     * @var string
     */
    public $docType;


    /**
     * @param mixed|null $id
     * @param array $data
     */
    public function __construct($id = null, array $data)
    {
        $this->setId($id);

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}