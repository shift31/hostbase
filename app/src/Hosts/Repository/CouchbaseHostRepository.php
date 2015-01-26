<?php namespace Hostbase\Hosts\Repository;

use Hostbase\Hosts\HostHelper;
use Hostbase\Repository\CouchbaseRepository;


/**
 * Class CouchbaseHostRepository
 * @package Hostbase\Hosts\Repository
 */
class CouchbaseHostRepository extends CouchbaseRepository implements HostRepository
{
    use HostHelper;
}