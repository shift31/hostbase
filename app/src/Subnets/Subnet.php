<?php namespace Hostbase\Subnets;

use Hostbase\Entity\DefaultEntity;


/**
 * Class Subnet
 *
 * @package Hostbase\Subnets
 */
class Subnet extends DefaultEntity
{
    /**
     * @var string
     */
    protected static $idField = 'network';

    /**
     * @var string
     */
    protected static $docType = 'subnet';

    /**
     * @var string
     */
    public $network;

    /**
     * @var string
     */
    public $netmask;

    /**
     * @var string
     */
    public $gateway;

    /**
     * @var int
     */
    public $cidr;


    /**
     * use CIDR notation for id
     *
     * @return string
     */
    public function getId()
    {
        return "{$this->network}/{$this->cidr}";
    }

} 