<?php namespace Hostbase\Hosts\Finder;

use Hostbase\Finder\ElasticsearchFinder;


class ElasticsearchHostsFinder extends ElasticsearchFinder implements HostsFinder {

    /**
     * The entity name/document type.  Used as the key prefix.
     *
     * @var string $entityName
     */
    static protected $entityName = 'host';


    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'fqdn';
} 