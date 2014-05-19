<?php namespace Hostbase\Subnet;

use Hostbase\ElasticsearchFinder;


class ElasticsearchSubnetFinder extends ElasticsearchFinder implements SubnetFinder {

    /**
     * The entity name/document type.  Used as the key prefix.
     *
     * @var string $entityName
     */
    static protected $entityName = 'subnet';


    /**
     * The default elasticsearch field.
     *
     * @var string $defaultSearchField
     */
    static protected $defaultSearchField = 'network';
} 