<?php namespace Hostbase;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Support\ServiceProvider;
use Doctrine\KeyValueStore\EntityManager;
use Doctrine\KeyValueStore\Mapping\AnnotationDriver;
use Doctrine\KeyValueStore\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationReader;


/**
 * Class DoctrineKeyValueStoreServiceProvider
 * @package Hostbase
 */
class DoctrineKeyValueStoreServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $cache = new ArrayCache;

        $storage = new NewCouchbaseStorage($this->app->make(\CouchbaseBucket::class));

        AnnotationRegistry::registerFile(base_path('vendor/doctrine/key-value-store/lib/Doctrine/KeyValueStore/Mapping/Annotations/Entity.php'));
        AnnotationRegistry::registerFile(base_path('vendor/doctrine/key-value-store/lib/Doctrine/KeyValueStore/Mapping/Annotations/Id.php'));
        AnnotationRegistry::registerFile(base_path('vendor/doctrine/key-value-store/lib/Doctrine/KeyValueStore/Mapping/Annotations/Transient.php'));

        $reader = new AnnotationReader();
        $metadata = new AnnotationDriver($reader);
        $config = new Configuration();
        $config->setMappingDriverImpl($metadata);
        $config->setMetadataCache($cache);

        $entityManager = new EntityManager($storage, $config);

        $this->app->instance(EntityManager::class, $entityManager);
        $this->app->alias(EntityManager::class, 'doctrine');
    }


    public function register()
    {
    }
}