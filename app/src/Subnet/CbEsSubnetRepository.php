<?php namespace Hostbase\Subnet;

use Hostbase\CouchbaseRepository;
use Validator;


class CouchbaseSubnetRepository extends CouchbaseRepository implements SubnetRepository
{

    /**
     * @var string $resourceName
     */
    static protected $entityName = 'subnet';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'network';

    /**
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'network';


    /**
     * @param array $data
     *
     * @param null  $subnet
     *
     * @throws \Exception
     * @return mixed
     */
    public function store(array $data, $subnet = null)
    {

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

        $subnet = "{$data['network']}/{$data['cidr']}";

        return parent::store($data, $subnet);
    }


    /**
     * @param null $id
     * @param array $data
     * @return \Hostbase\Entity\Entity|Subnet
     */
    public function makeNewEntity($id = null, array $data = [])
    {
        return new Subnet($id, $data);
    }
}