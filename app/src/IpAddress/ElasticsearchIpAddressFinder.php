<?php namespace Hostbase\IpAddress;

use Hostbase\Finder\ElasticsearchFinder;


class ElasticsearchIpAddressFinder extends ElasticsearchFinder implements IpAddressFinder {

    /**
     * The entity name/document type.  Used as the key prefix.
     *
     * @var string $entityName
     */
    static protected $entityName = 'ipAddress';


    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'ipAddress';
} 