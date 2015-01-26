<?php namespace Hostbase\Entity;

use Doctrine\Common\Inflector\Inflector;
use Hostbase\Entity\Exceptions\InvalidEntity;


/**
 * Class DefaultEntity
 *
 * @package Hostbase\Entity
 */
abstract class DefaultEntity implements Entity
{
    /**
     * @var string
     */
    protected static $idField = null;

    /**
     * @var string
     */
    protected static $docType = null;


    /**
     * @param string|null $id
     * @param array       $data
     *
     * @throws \Exception
     */
    public function __construct($id = null, array $data)
    {
        if (static::$idField === null || static::$docType === null) {
            throw new InvalidEntity("The static 'idField' and 'docType' properties must be set on all subclasses of " . __CLASS__);
        }

        if ( ! isset($data[static::$idField])) {
            throw new InvalidEntity(ucfirst(Inflector::pluralize(static::$docType)) . ' must have a value for ' . "'" . static::$idField . "'");
        }

        foreach ($data as $key => $value) {
            if ($key == 'docType') {
                continue;
            }
            $this->$key = $value;
        }
    }


    /**
     * @inheritdoc
     */
    public static function getIdField()
    {
        return static::$idField;
    }


    /**
     * @inheritdoc
     */
    public static function getDocType()
    {
        return static::$docType;
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->{static::$idField};
    }


    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $entityAsArray = get_object_vars($this);
        $entityAsArray['docType'] = static::$docType;

        return $entityAsArray;
    }
}