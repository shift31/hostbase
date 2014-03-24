<?php namespace Hostbase\Host;


interface HostRepository
{

    /**
     * @param array $data
     *
     * @return void
     */
    public function encryptAdminPassword(array &$data);


    /**
     * @param array $data
     *
     * @return void
     */
    public function decryptAdminPassword(array &$data);
}