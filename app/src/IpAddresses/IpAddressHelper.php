<?php namespace Hostbase\IpAddresses;

use Hostbase\Entity\Exceptions\InvalidEntity;
use Validator;


/**
 * Class IpAddressHelper
 *
 * @package Hostbase\IpAddresses
 */
trait IpAddressHelper
{
    /**
     * @param array $data
     *
     * @return IpAddress
     * @throws \Exception
     */
    public function makeEntity(array $data)
    {
        $validator = Validator::make(
            $data,
            [
                'subnet'    => 'required',
                'ipAddress' => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new InvalidEntity(join('; ', $validator->messages()->all()));
        }

        return new IpAddress(null, $data);
    }


    /**
     * @return string
     */
    public function getEntityIdField()
    {
        return IpAddress::getIdField();
    }


    /**
     * @return string
     */
    public function getEntityDocType()
    {
        return IpAddress::getDocType();
    }
} 