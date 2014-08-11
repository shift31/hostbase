<?php namespace Hostbase\Subnet;

use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Hostbase\Resource\BaseResourceService;
use Validator;


class SubnetService extends BaseResourceService {

    use SubnetMaker;


    /**
     * @var string $entityName
     */
    static protected $entityName = 'subnet';

    /**
     * @var string $idField
     */
    static protected $idField = 'network';

    /**
     * @var SubnetRepository
     */
    protected $repository;

    /**
     * @var SubnetFinder
     */
    protected $finder;


    /**
     * @param SubnetRepository $repository
     * @param SubnetFinder $finder
     */
    public function __construct(SubnetRepository $repository, SubnetFinder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;
    }


    /**
     * @param Entity $subnet
     * @throws \Exception
     * @throws InvalidEntity
     * @return Subnet
     */
    public function store(Entity $subnet)
    {
        if (! $subnet instanceof Subnet) {
            throw new InvalidEntity('Expected $subnet to be an instance of Subnet');
        }

        $data = $subnet->getData();

        $validator = Validator::make(
            $data,
            [
                'network' => 'required',
                'netmask' => 'required',
                'gateway' => 'required',
                'cidr'    => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new \Exception(join('; ', $validator->messages()->all()));
        }

        return $this->repository->store($subnet);
    }
} 