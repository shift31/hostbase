<?php namespace Hostbase\Host;

use Crypt;
use Log;
use Hostbase\CbEsRepository;


class CbEsHostRepository extends CbEsRepository implements HostRepository
{

    /**
     * @var string $resourceName
     */
    static protected $resourceName = 'host';

    /**
     * @var string $keySuffixField
     */
    static protected $keySuffixField = 'fqdn';

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
                $this->decryptAdminPassword($host);
            }
        }

        return $hosts;
    }


    /**
     * @param string|null $id
     *
     * @throws \Exception
     * @return array|null
     */
    public function show($id = null)
    {
        $data = parent::show($id);

        if ($id != null) $this->decryptAdminPassword($data);

        return $data;
    }


    /**
     * @param array $data
     *
     * @param null  $fqdn
     *
     * @throws \Exception
     * @return mixed
     */
    public function store(array $data, $fqdn = null)
    {
        if (!isset($data['fqdn'])) {
            throw new \Exception("Host must have a value for 'fqdn'");
        }

        // generate hostname and domain if they don't already exist
        if (!isset($data['hostname']) && !isset($data['domain'])) {
            $fqdnParts = explode('.', $data['fqdn'], 2);
            //Log::debug('$fqdnParts:' . print_r($fqdnParts, true));
            $data['hostname'] = $fqdnParts[0];
            $data['domain'] = $fqdnParts[1];
        }

        // set document type and creation time
        $data['docType'] = 'host';
        $data['createdDateTime'] = date('c');

        // encrypt admin password
        $this->encryptAdminPassword($data);

        return parent::store($data);
    }


    /**
     * @param string $fqdn
     * @param array  $data
     *
     * @throws \Exception
     * @return mixed
     */
    public function update($fqdn, array $data)
    {
        // encrypt admin password
        $this->encryptAdminPassword($data);

        return parent::update($fqdn, $data);
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