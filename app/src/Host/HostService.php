<?php namespace Hostbase\Host;

use Crypt;
use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Hostbase\Resource\BaseResourceService;
use Log;


class HostService extends BaseResourceService {

    use HostMaker;

    /**
     * @var string $entityName
     */
    static protected $entityName = 'host';

    /**
     * @var string $idField
     */
    static protected $idField = 'fqdn';

    /**
     * @var \Hostbase\Host\HostRepository
     */
    protected $repository;

    /**
     * @var HostFinder
     */
    protected $finder;

    /**
     * @param HostRepository $repository
     * @param HostFinder $finder
     */
    public function __construct(HostRepository $repository, HostFinder $finder)
    {
        $this->repository = $repository;
        $this->finder = $finder;
    }


    /**
     * @param null|string $id
     * @return Host
     */
    public function showOne($id)
    {
        $host = $this->repository->getOne($id);

        $data = $host->getData();

        $this->decryptAdminPassword($data);

        $host->setData($data);

        return $host;
    }


    /**
     * @param array $ids
     * @return array
     */
    public function showMany(array $ids)
    {
        $hosts = $this->repository->getMany($ids);

        foreach ($hosts as &$host) {
            $data = $host->getData();

            $this->decryptAdminPassword($data);

            $host->setData($data);
        }

        return $hosts;
    }


    /**
     * @param Entity $host
     * @return Host
     * @throws InvalidEntity
     * @throws HostMissingFqdn
     */
    public function store(Entity $host)
    {
        if (! $host instanceof Host) {
            throw new InvalidEntity('Expected $host to be an instance of Host');
        }

        $fqdn = $host->getFqdn();

        if ($fqdn === null) {
            throw new HostMissingFqdn("Host must have a value for 'fqdn'");
        }

        $data = $host->getData();

        // generate hostname and domain if they don't already exist
        if (!isset($data['hostname']) && !isset($data['domain'])) {
            $fqdnParts = explode('.', $data['fqdn'], 2);
            //Log::debug('$fqdnParts:' . print_r($fqdnParts, true));
            $data['hostname'] = $fqdnParts[0];
            $data['domain'] = $fqdnParts[1];
        }

        // encrypt admin password
        $this->encryptAdminPassword($data);

        $host->setData($data);

        return $this->repository->store($host);
    }


    /**
     * @param Entity $host
     * @throws InvalidEntity
     * @return Host
     */
    public function update(Entity $host)
    {
        if (! $host instanceof Host) {
            throw new InvalidEntity('Expected $host to be an instance of Host');
        }

        $data = $host->getData();

        // encrypt admin password
        $this->encryptAdminPassword($data);

        $host->setData($data);

        return $this->repository->update($host);
    }


    // todo - move to Host entity

    /**
     * @param array $data
     */
    protected function encryptAdminPassword(array &$data)
    {
        if (isset($data['adminCredentials'])) {
            $data['adminCredentials']['encryptedPassword'] = Crypt::encrypt($data['adminCredentials']['password']);
            unset($data['adminCredentials']['password']);
        }
    }


    /**
     * @param array $data
     */
    protected function decryptAdminPassword(array &$data)
    {
        if (isset($data['adminCredentials'])) {
            Log::debug("Decrypting admin password for {$data['fqdn']}");
            $data['adminCredentials']['password'] = Crypt::decrypt($data['adminCredentials']['encryptedPassword']);
            unset($data['adminCredentials']['encryptedPassword']);
        }
    }
} 