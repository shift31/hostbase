<?php namespace Hostbase;

use Doctrine\KeyValueStore\Mapping\Annotations\Entity;
use Doctrine\KeyValueStore\Mapping\Annotations\Id;


/**
 * Class TestEntity
 * @package Hostbase
 *
 * @Entity
 */
class TestEntity {

    /**
     * @var string
     * @Id
     */
    private $name;

    /**
     * @var string
     */
    public $foo;


    public function __construct($name, $foo)
    {
        $this->name = $name;
        $this->foo = $foo;
    }
}