<?php namespace Hostbase\Host;

use Crypt;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Log;
use Hostbase\Entity\Entity;
use Hostbase\CouchbaseRepository;


class CouchbaseHostRepository extends CouchbaseRepository implements HostRepository
{
    use HostMaker;


    /**
     * @var string $resourceName
     */
    static protected $entityName = 'host';

    /**
     * @var string $keySuffixField
     */
    static protected $idField = 'fqdn';


//    /**
//     * @param string|null $id
//     *
//     * @throws \Exception
//     * @return Host|null
//     */
//    public function show($id = null)
//    {
//        $hostOrList = parent::show($id);
//
//        if ($id !== null) {
//            $data = $hostOrList->getData();
//
//            $this->decryptAdminPassword($data);
//
//            $hostOrList->setData($data);
//        }
//
//        return $hostOrList;
//    }


//    /**
//     * @param Entity $host
//     * @return Host
//     * @throws InvalidEntity
//     * @throws HostMissingFqdn
//     */
//    public function store(Entity $host)
//    {
//        if (! $host instanceof Host) {
//            throw new InvalidEntity('Expected $host to be an instance of Host');
//        }
//
//        $fqdn = $host->getFqdn();
//
//        if ($fqdn === null) {
//            throw new HostMissingFqdn("Host must have a value for 'fqdn'");
//        }
//
//        $data = $host->getData();
//
//        // generate hostname and domain if they don't already exist
//        if (!isset($data['hostname']) && !isset($data['domain'])) {
//            $fqdnParts = explode('.', $data['fqdn'], 2);
//            //Log::debug('$fqdnParts:' . print_r($fqdnParts, true));
//            $data['hostname'] = $fqdnParts[0];
//            $data['domain'] = $fqdnParts[1];
//        }
//
//        // encrypt admin password
//        $this->encryptAdminPassword($data);
//
//        $host->setData($data);
//
//        return parent::store($host);
//    }


//    /**
//     * @param Entity $host
//     * @return Host
//     */
//    public function update(Entity $host)
//    {
//        $data = $host->getData();
//
//        // encrypt admin password
//        $this->encryptAdminPassword($data);
//
//        $host->setData($data);
//
//        return parent::update($host);
//    }


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