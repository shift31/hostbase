<?php namespace Hostbase\Hosts;

use Crypt;
use Hostbase\Hosts\Finder\HostsFinder;
use Hostbase\Hosts\Repository\HostRepository;
use Hostbase\Services\DefaultResourceService;
use Log;


/**
 * Class HostsService
 * @package Hostbase\Hosts
 */
class HostsService extends DefaultResourceService
{
    use HostHelper;


    /**
     * @var \Hostbase\Hosts\Repository\HostRepository
     */
    protected $repository;

    /**
     * @var HostsFinder
     */
    protected $finder;


    /**
     * @param HostRepository $repository
     * @param HostsFinder $finder
     */
    public function __construct(HostRepository $repository, HostsFinder $finder)
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

        $this->decryptAdminPassword($host);

        return $host;
    }


    /**
     * @param array $ids
     * @return array
     */
    public function showMany(array $ids)
    {
        $hosts = $this->repository->getMany($ids);

        foreach ($hosts as $host) {
            $this->decryptAdminPassword($host);
        }

        return $hosts;
    }


    /**
     * @param array $data
     * @return Host
     * @throws HostMissingFqdn
     */
    public function store(array $data)
    {
        $host = $this->makeEntity($data);

        // encrypt admin password
        $this->encryptAdminPassword($host);

        $host->createdDateTime = date('c');

        return $this->repository->store($host);
    }


    /**
     * @param string $id
     * @param array $data
     * @return Host
     */
    public function update($id, array $data)
    {
        $host = $this->repository->getOne($id);

        if (isset($data['adminCredentials'])) {
            $this->encryptAdminPassword($host);
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                // keys should be removed when nullified
                unset($host->$key);
            } else {
                $host->$key = $value;
            }
        }

        $host->updatedDateTime = date('c');

        return $this->repository->update($host);
    }


    /**
     * @param Host $host
     */
    protected function encryptAdminPassword(Host $host)
    {
        if (isset($host->adminCredentials)) {
            Log::debug("Encrypting admin password for {$host->fqdn}");
            $host->adminCredentials['encryptedPassword'] = Crypt::encrypt($host->adminCredentials['password']);
            unset($host->adminCredentials['password']);
        }
    }


    /**
     * @param Host $host
     */
    protected function decryptAdminPassword(Host $host)
    {
        if (isset($host->adminCredentials)) {
            Log::debug("Decrypting admin password for {$host->fqdn}");
            $host->adminCredentials['password'] = Crypt::decrypt($host->adminCredentials['encryptedPassword']);
            unset($host->adminCredentials['encryptedPassword']);
        }
    }
}