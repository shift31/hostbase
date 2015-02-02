<?php namespace Hostbase\IpAddresses;

use Hostbase\IpAddresses\Finder\IpAddressesFinder;
use Hostbase\IpAddresses\Repository\IpAddressRepository;
use Hostbase\Services\DefaultResourceService;


/**
 * Class IpAddressesService
 *
 * @package Hostbase\IpAddresses
 */
class IpAddressesService extends DefaultResourceService
{
    use IpAddressHelper;


    /**
     * @var IpAddressRepository
     */
    protected $repository;

    /**
     * @var IpAddressesFinder
     */
    protected $finder;


    /**
     * @param IpAddressRepository $repository
     * @param IpAddressesFinder $finder
     */
    public function __construct(IpAddressRepository $repository, IpAddressesFinder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;
    }
} 