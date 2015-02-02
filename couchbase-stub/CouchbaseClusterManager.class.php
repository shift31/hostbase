<?php
/**
 * File for the CouchbaseClusterManager class.
 *
 * @author Brett Lawson <brett19@gmail.com>
 */

/**
 * Class exposing the various available management operations that can be
 * performed on a cluster.
 *
 * @package Couchbase
 */
class CouchbaseClusterManager {
    /**
     * @var _CouchbaseCluster
     * @ignore
     *
     * Pointer to our C binding backing class.
     */
    private $_me;

    /**
     * Constructs a cluster manager connection.
     *
     * @param string $connstr A connection string to connect with.
     * @param string $username The username to authenticate with.
     * @param string $password The password to authenticate with.
     *
     * @private
     * @ignore
     */
    public function __construct($connstr, $username, $password) {
        $this->_me = new _CouchbaseCluster($connstr, $username, $password);
    }

    /**
     * Lists all buckets on this cluster.
     *
     * @return mixed
     */
    public function listBuckets() {
        $path = "/pools/default/buckets";
        $res = $this->_me->http_request(2, 1, $path, NULL, 2);
        return json_decode($res, true);
    }

    /**
     * Creates a new bucket on this cluster.
     *
     * @param string $name The bucket name.
     * @param array $opts The options for this bucket.
     * @return mixed
     */
    public function createBucket($name, $opts = array()) {
        $myOpts = array(
            'name' => $name,
            'authType' => 'sasl',
            'bucketType' => 'couchbase',
            'ramQuotaMB' => 100,
            'replicaNumber' => 1
        );
        foreach($opts as $k => $v) {
            $myOpts[$k] = $v;
        }

        $path = "/pools/default/buckets";
        $args = array();
        foreach ($opts as $option => $value) {
            array_push($args, $option . '=' . $value);
        }
        $path .= '?' . implode('&', $args);

        $res = $this->_me->http_request(2, 2, $path, NULL, 2);
        return json_decode($res, true);
    }

    /**
     * Deletes a bucket from the cluster.
     *
     * @param string $name
     * @return mixed
     */
    public function removeBucket($name) {
        $path = "/pools/default/buckets/" + $name;
        $res = $this->_me->http_request(2, 4, $path, NULL, 2);
        return json_decode($res, true);
    }

    /**
     * Retrieves cluster status information
     *
     * Returns an associative array of status information as seen
     * on the cluster.  The exact structure of the returned data
     * can be seen in the Couchbase Manual by looking at the
     * cluster /info endpoint.
     *
     * @return mixed The status information.
     *
     * @throws CouchbaseException
     */
    public function info() {
        $path = "/pools/default";
        $res = $this->_me->http_request(2, 1, $path, NULL, 2);
        return json_decode($res, true);
    }
}