<?php namespace Hostbase\IpAddress;

use Hostbase\CouchbaseRepository;


class CouchbaseIpAddressRepository extends CouchbaseRepository implements IpAddressRepository
{
    use IpAddressMaker;


    /**
     * @var string $resourceName
     */
    static protected $entityName = 'ipAddress';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'ipAddress';
}