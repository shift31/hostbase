<?php namespace Hostbase\Subnets\Finder;

use Hostbase\Finder\ElasticsearchFinder;


class ElasticsearchSubnetsFinder extends ElasticsearchFinder implements SubnetsFinder {

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