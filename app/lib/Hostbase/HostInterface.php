<?php

namespace Hostbase;


interface HostInterface {

    /**
     * @param string $query
     * @param bool $showData
     * @return array
     */
    public function search($query, $showData = false);

    /**
     * @param string|null $fqdn
     * @return array|null
     */
    public function get($fqdn = null);

    /**
     * @param string $fqdn
     * @param array $data
     * @return mixed
     */
    public function add($fqdn, array $data);

    /**
     * @param string $fqdn
     * @param array $data
     * @return mixed
     */
    public function modify($fqdn, array $data);


    /**
     * @param string $fqdn
     * @return mixed
     */
    public function remove($fqdn);
}