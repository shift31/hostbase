<?php namespace Hostbase\Hosts\Repository;

use Hostbase\Hosts\HostMaker;
use Hostbase\Repository\CouchbaseRepository;


class CouchbaseHostRepository extends CouchbaseRepository implements HostRepository
{
    use HostMaker;


    /**
     * @var string $resourceName
     */
    static protected $entityName = 'host';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'fqdn';
}