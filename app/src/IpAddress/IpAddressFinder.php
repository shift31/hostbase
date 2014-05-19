<?php namespace Hostbase\IpAddress;


interface IpAddressFinder
{
    public function search($query, $limit);
}