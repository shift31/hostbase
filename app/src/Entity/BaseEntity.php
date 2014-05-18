<?php namespace Hostbase\Entity;


abstract class BaseEntity implements Entity
{

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var array
     */
    protected $data;


    /**
     * @param mixed|null $id
     * @param array $data
     */
    public function __construct($id = null, array $data = [])
    {
        if (isset($id) && count($data) !== 0) {
            $this->setId($id);
            $this->setData($data);
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
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}