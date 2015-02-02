<?php namespace Hostbase\Hosts;


/**
 * Class HostHelper
 * @package Hostbase\Hosts
 */
trait HostHelper {

    /**
     * @param array $data
     * @return Host
     */
    public function makeEntity(array $data)
    {
        return new Host(null, $data);
    }


    /**
     * @return string
     */
    public function getEntityIdField()
    {
        return Host::getIdField();
    }


    /**
     * @return string
     */
    public function getEntityDocType()
    {
        return Host::getDocType();
    }
} 