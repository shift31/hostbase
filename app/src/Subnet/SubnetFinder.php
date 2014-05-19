<?php namespace Hostbase\Subnet;


interface SubnetFinder
{
    public function search($query, $limit);
}