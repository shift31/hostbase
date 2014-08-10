<?php namespace Hostbase\Subnet;

use Hostbase\Entity\Entity;
use Hostbase\Repository\CouchbaseRepository;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;


class CouchbaseSubnetRepository extends CouchbaseRepository implements SubnetRepository
{
    use SubnetMaker;


    /**
     * @var string $resourceName
     */
    static protected $entityName = 'subnet';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'network';


    /**
     * @param Entity $subnet
     *
     * @throws EntityAlreadyExists
     * @return Entity
     */
    public function store(Entity $subnet)
    {
        $data = $subnet->getData();

        // use CIDR notation for id
        $id = "{$data['network']}/{$data['cidr']}";

        $key = $this->makeKey($id);
        $subnet->setId($key);

        // set document type and creation time
        $data['docType'] = static::$entityName;
        $data['createdDateTime'] = date('c');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        if (! $this->cb->save($this->makeCbDocument($key, $data), ['override' => false])) {
            throw new EntityAlreadyExists("'$id' already exists");
        }

        $subnet->setData($data);

        return $subnet;
    }
}