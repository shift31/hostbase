<?php namespace Hostbase\IpAddresses\Finder;

use Hostbase\Finder\ElasticsearchFinder;


class ElasticsearchIpAddressesFinder extends ElasticsearchFinder implements IpAddressesFinder {

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