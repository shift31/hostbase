<?php namespace Hostbase\Hosts;

use Crypt;
use Hostbase\Entity\Entity;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Hostbase\Hosts\Finder\HostsFinder;
use Hostbase\Hosts\Repository\HostRepository;
use Hostbase\Services\BaseResourceService;
use Log;


class HostsService extends BaseResourceService {

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

        // generate hostname and domain if they don't already exist
        if (!isset($host->hostname) && !isset($host->domain)) {
            $fqdnParts = explode('.', $host->fqdn, 2);
            //Log::debug('$fqdnParts:' . print_r($fqdnParts, true));
            $host->hostname = $fqdnParts[0];
            $host->domain = $fqdnParts[1];
        }

        // encrypt admin password
        $this->encryptAdminPassword($host);

        return $this->repository->store($host);
    }


    /**
     * @param string $id
     * @param array $data
     * @return Host
     */
    public function update($id, array $data)
    {
        // encrypt admin password
        $this->encryptAdminPassword($data);

        return parent::update($id, $data);
    }


    /**
     * @param Host $host
     */
    protected function encryptAdminPassword(Host $host)
    {
        if (isset($host->adminCredentials)) {
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