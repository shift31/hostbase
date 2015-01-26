<?php namespace Hostbase\IpAddresses\Repository;

use Hostbase\IpAddresses\IpAddressHelper;
use Hostbase\Repository\CouchbaseRepository;


/**
 * Class CouchbaseIpAddressRepository
 *
 * @package Hostbase\IpAddresses\Repository
 */
class CouchbaseIpAddressRepository extends CouchbaseRepository implements IpAddressRepository
{
    use IpAddressHelper;
}