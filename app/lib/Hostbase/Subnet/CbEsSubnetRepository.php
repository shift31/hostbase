<?php namespace Hostbase\Subnet;

use Hostbase\CbEsRepository;
use Validator;


class CbEsSubnetRepository extends CbEsRepository implements SubnetRepository
{

    /**
     * @var string $resourceName
     */
    static protected $resourceName = 'subnet';

    /**
     * @var string $keySuffixField
     */
    static protected $keySuffixField = 'network';

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
}