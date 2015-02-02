<?php namespace Hostbase\Subnets;

use Hostbase\Services\DefaultResourceService;
use Hostbase\Subnets\Finder\SubnetsFinder;
use Hostbase\Subnets\Repository\SubnetRepository;


/**
 * Class SubnetsService
 *
 * @package Hostbase\Subnets
 */
class SubnetsService extends DefaultResourceService
{
    use SubnetHelper;


    /**
     * @var SubnetRepository
     */
    protected $repository;

    /**
     * @var SubnetsFinder
     */
    protected $finder;


    /**
     * @param SubnetRepository $repository
     * @param SubnetsFinder $finder
     */
    public function __construct(SubnetRepository $repository, SubnetsFinder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;
    }
} 