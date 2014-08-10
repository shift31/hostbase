<?php namespace Hostbase\Host;

use Hostbase\Entity\BaseEntity;


class Host extends BaseEntity
{
    /**
     * @var string
     */
    protected $fqdn;


    /**
     * @param string $fqdn
     */
    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
        $this->data['fqdn'] = $fqdn;
    }


    /**
     * @return string
     */
    public function getFqdn()
    {
        return $this->fqdn;
    }


    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['fqdn'])) {
            $this->fqdn = $data['fqdn'];
        } else {
            $this->data['fqdn'] = $this->fqdn;
        }
    }

} 