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
    public function show($fqdn = null);

    /**
     * @param array $data
     * @throws \Exception
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param string $fqdn
     * @param array $data
     * @return mixed
     */
    public function update($fqdn, array $data);


    /**
     * @param string $fqdn
     * @return mixed
     */
    public function destroy($fqdn);
}