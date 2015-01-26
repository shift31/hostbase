<?php namespace Hostbase\IpAddresses;

use Hostbase\Entity\DefaultEntity;


/**
 * Class IpAddress
 *
 * @package Hostbase\IpAddresses
 */
class IpAddress extends DefaultEntity
{
    /**
     * @var string
     */
    protected static $idField = 'ipAddress';

    /**
     * @var string
     */
    protected static $docType = 'ipAddress';
} 