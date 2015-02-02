<?php namespace Hostbase\Subnets\Repository;

use Hostbase\Repository\CouchbaseRepository;
use Hostbase\Subnets\SubnetHelper;


/**
 * Class CouchbaseSubnetRepository
 *
 * @package Hostbase\Subnets\Repository
 */
class CouchbaseSubnetRepository extends CouchbaseRepository implements SubnetRepository
{
    use SubnetHelper;
}