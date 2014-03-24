<?php namespace Hostbase\IpAddress;

use Hostbase\CbEsRepository;
use Validator;


class CbEsIpAddressRepository extends CbEsRepository implements IpAddressRepository
{
    /**
     * @var string $resourceName
     */
    static protected $entityName = 'ipAddress';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'ipAddress';

    /**
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'ipAddress';


    /**
     * @param array $data
     *
     * @param null  $ipAddress
     *
     * @throws \Exception
     * @return mixed
     */
    public function store(array $data, $ipAddress = null)
    {
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

        return parent::store($data);
    }
}