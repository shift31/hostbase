<?php namespace Hostbase\IpAddresses;

use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Hostbase\IpAddresses\Finder\IpAddressesFinder;
use Hostbase\IpAddresses\Repository\IpAddressRepository;
use Hostbase\Services\BaseResourceService;
use Validator;


class IpAddressesService extends BaseResourceService {

    use IpAddressMaker;


    /**
     * @var string $entityName
     */
    static protected $entityName = 'ipAddress';

    /**
     * @var string $idField
     */
    static protected $idField = 'ipAddress';

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


    /**
     * @param Entity $ipAddress
     * @throws \Exception
     * @throws InvalidEntity
     * @return IpAddress
     */
    public function store(Entity $ipAddress)
    {
        if (! $ipAddress instanceof IpAddress) {
            throw new InvalidEntity('Expected $ipAddress to be an instance of IpAddress');
        }

        $data = $ipAddress->getData();

        $validator = Validator::make(
            $data,
            [
                'subnet'    => 'required',
                'ipAddress' => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new \Exception(join('; ', $validator->messages()->all()));
        }

        return $this->repository->store($ipAddress);
    }
} 