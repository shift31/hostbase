<?php namespace Hostbase;

use Doctrine\KeyValueStore\NotFoundException;
use Doctrine\KeyValueStore\Storage\Storage;


class NewCouchbaseStorage implements Storage
{
    /**
     * @var \CouchbaseBucket
     */
    protected $bucket;

    /**
     * Constructor
     *
     * @param \CouchbaseBucket $bucket
     */
    public function __construct(\CouchbaseBucket $bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPartialUpdates()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCompositePrimaryKeys()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function requiresCompositePrimaryKeys()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function insert($storageName, $key, array $data)
    {
        $this->bucket->insert($key, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function update($storageName, $key, array $data)
    {
        $this->bucket->replace($key, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($storageName, $key)
    {
        $this->bucket->remove($key);
    }

    /**
     * {@inheritDoc}
     */
    public function find($storageName, $key)
    {
        $result = $this->bucket->get($key);

        if ($result === null) {
            throw new NotFoundException();
        }

        $value = (array) $result->value;

        return $value;
    }

    /**
     * Return a name of the underlying storage.
     *
     * @return string
     */
    public function getName()
    {
        return 'couchbase';
    }
}
