<?php namespace Hostbase\Host;

use Crypt;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Log;
use Hostbase\Entity\Entity;
use Hostbase\CbEsRepository;


class CbEsHostRepository extends CbEsRepository implements HostRepository
{

    /**
     * @var string $resourceName
     */
    static protected $entityName = 'host';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'fqdn';

    /**
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'fqdn';


    /**
     * @param string $query
     * @param int    $limit
     * @param bool   $showData
     *
     * @throws \Exception
     * @return array
     */
    public function search($query, $limit = 10000, $showData = false)
    {
        $hosts = parent::search($query, $limit, $showData);

        if ($showData === true) {
            foreach ($hosts as &$host) {

                if ($host instanceof Host) {
                    $data = $host->getData();

                    $this->decryptAdminPassword($data);

                    $host->setData($data);
                }
            }
        }

        return $hosts;
    }


    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return Host|null
     */
    public function show($id = null)
    {
        $host = parent::show($id);

        $data = $host->getData();

        $this->decryptAdminPassword($data);

        $host->setData($data);

        return $host;
    }


    /**
     * @param Entity $host
     * @return Host
     * @throws InvalidEntity
     * @throws HostMissingFqdnException
     */
    public function store(Entity $host)
    {
        if (! $host instanceof Host) {
            throw new InvalidEntity('Expected $host to be an instance of Host');
        }

        $fqdn = $host->getFqdn();

        if ($fqdn === null) {
            throw new HostMissingFqdnException("Host must have a value for 'fqdn'");
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

        return parent::store($host);
    }


    /**
     * @param Entity $host
     * @return Host
     */
    public function update(Entity $host)
    {
        $data = $host->getData();

        // encrypt admin password
        $this->encryptAdminPassword($data);

        $host->setData($data);

        return parent::update($host);
    }


    /**
     * @param string|null $fqdn
     * @param array $data
     * @return Host
     */
    public function makeNewEntity($fqdn = null, array $data = [])
    {
        return new Host($fqdn, $data);
    }


    /**
     * @param array $data
     */
    public function encryptAdminPassword(array &$data)
    {
        if (isset($data['adminCredentials'])) {
            $data['adminCredentials']['encryptedPassword'] = Crypt::encrypt($data['adminCredentials']['password']);
            unset($data['adminCredentials']['password']);
        }
    }


    /**
     * @param array $data
     */
    public function decryptAdminPassword(array &$data)
    {
        if (isset($data['adminCredentials'])) {
            Log::debug("Decrypting admin password for {$data['fqdn']}");
            $data['adminCredentials']['password'] = Crypt::decrypt($data['adminCredentials']['encryptedPassword']);
            unset($data['adminCredentials']['encryptedPassword']);
        }
    }
}