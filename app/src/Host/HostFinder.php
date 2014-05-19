<?php namespace Hostbase\Host;


interface HostFinder
{
    public function search($query, $limit);
}