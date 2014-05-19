<?php namespace Hostbase\Host;

use Hostbase\ElasticsearchFinder;


class ElasticsearchHostFinder extends ElasticsearchFinder implements HostFinder {

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