<?php namespace Hostbase\Hosts;

use Hostbase\Entity\DefaultEntity;


/**
 * Class Host
 * @package Hostbase\Hosts
 */
class Host extends DefaultEntity
{
    /**
     * @var string
     */
    protected static $idField = 'fqdn';

    /**
     * @var string
     */
    protected static $docType = 'host';

    /**
     * @var string
     */
    public $fqdn;

    /**
     * @var array
     */
    public $adminCredentials;


    /**
     * @param null $fqdn
     * @param array $data
     * @throws \Exception
     */
    public function __construct($fqdn = null, array $data)
    {
        parent::__construct($fqdn, $data);

        // generate hostname and domain if they don't already exist
        if ( ! isset($this->hostname) && ! isset($this->domain)) {
            $fqdnParts = explode('.', $this->fqdn, 2);
            $this->hostname = $fqdnParts[0];
            $this->domain = $fqdnParts[1];
        }
    }


    /**
     * @param string $fqdn
     */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
    }


    /**
     * @return string
     */
    public function getFqdn()
    {
        return $this->fqdn;
    }
} 