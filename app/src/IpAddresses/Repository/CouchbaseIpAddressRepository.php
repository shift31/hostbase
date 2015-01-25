<?php namespace Hostbase\IpAddresses\Repository;

use Hostbase\IpAddresses\IpAddressMaker;
use Hostbase\Repository\CouchbaseRepository;


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