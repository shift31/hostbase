<?php namespace Hostbase\Hosts;

use Hostbase\Entity\BaseEntity;


class Host extends BaseEntity
{
    /**
     * @var string
     */
    public $fqdn;

    /**
     * @var array
     */
    public $adminCredentials;


    public function __construct($fqdn = null, array $data)
    {
        parent::__construct($fqdn, $data);

        $this->setFqdn($data['fqdn']);
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        parent::setId($id);
        $this->setFqdn($id);
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->getFqdn();
    }


    /**
     * @param string $fqdn
     */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
        parent::setId($fqdn);
    }


    /**
     * @return string
     */
    public function getFqdn()
    {
        return $this->fqdn;
    }
} 