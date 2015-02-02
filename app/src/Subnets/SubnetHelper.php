<?php namespace Hostbase\Subnets;

use Validator;


/**
 * Class SubnetHelper
 *
 * @package Hostbase\Subnets
 */
trait SubnetHelper
{
    /**
     * @param array $data
     *
     * @return Subnet
     * @throws \Exception
     */
    public function makeEntity(array $data)
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

        return new Subnet(null, $data);
    }


    /**
     * @return string
     */
    public function getEntityIdField()
    {
        return Subnet::getIdField();
    }


    /**
     * @return string
     */
    public function getEntityDocType()
    {
        return Subnet::getDocType();
    }
} 