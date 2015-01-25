<?php
/**
 * Various helpers for dealing with connection strings.
 *
 * @author Brett Lawson <brett19@gmail.com>
 */

/**
 * Normalizes a connection string object.
 *
 * @param $dsnObj A connstr object.
 * @return array
 *
 * @internal
 */
function _cbdsn_normalize($dsnObj) {
    $out = array();

    if (isset($dsnObj['scheme'])) {
        $out['scheme'] = $dsnObj['scheme'];
    } else {
        $out['scheme'] = '';
    }

    $out['hosts'] = array();
    if (isset($dsnObj['hosts'])) {
        if (is_string($dsnObj['hosts'])) {
            $dsnObj['hosts'] = array($dsnObj['hosts']);
        }

        foreach($dsnObj['hosts'] as $host) {
            if (is_string($host)) {
                $portPos = strstr($host, ':');
                if ($portPos) {
                    $hostName = substr($host, 0, $portPos);
                    $portNum = intval(substr($host, $portPos+1));
                    array_push($out['hosts'], array(
                        $hostName, $portNum
                    ));
                } else {
                    array_push($out['hosts'], $host);
                }
            } else {
                array_push($out['hosts'], $host);
            }
        }
    }

    if (isset($dsnObj['bucket'])) {
        $out['bucket'] = $dsnObj['bucket'];
    } else {
        $out['bucket'] = 'default';
    }

    if (isset($dsnObj['options'])) {
        $out['options'] = $dsnObj['options'];
    } else {
        $out['options'] = array();
    }

    return $out;
}

/**
 * Normalizes a connection string object or string.
 *
 * @param $dsn A connection string or connstr object.
 * @return array|string
 *
 * @internal
 */
function cbdsn_normalize($dsn) {
    if (is_string($dsn)) {
        return _cbdsn_stringify(
            _cbdsn_normalize(
                _cbdsn_parse($dsn)
            )
        );
    }
    return _cbdsn_normalize($dsn);
}

/**
 * Parses a connection string into a object.
 *
 * @param $dsn A connection string.
 * @return array
 *
 * @internal
 */
function _cbdsn_parse($dsn) {
    $out = array();

    if (!$dsn) {
        return $out;
    }

    preg_match("/((.*):\\/\\/)?([^\\/?]*)(\\/([^\\?]*))?(\\?(.*))?/", $dsn, $parts);
    if (isset($parts[2])) {
        $out['scheme'] = $parts[2];
    }
    if (isset($parts[3])) {
        $out['hosts'] = array();

        preg_match_all("/([^;\\,\\:]+)(:([0-9]*))?(;\\,)?/", $parts[3], $hosts, PREG_SET_ORDER);
        foreach($hosts as $host) {
            array_push($out['hosts'], array(
                $host[1],
                isset($host[3]) ? intval($host[3]) : 0
            ));
        }
    }
    if (isset($parts[5])) {
        $out['bucket'] = $parts[5];
    }
    if (isset($parts[7])) {
        $out['options'] = array();

        preg_match_all("/([^=]*)=([^&?]*)[&?]?/", $parts[7], $kvs, PREG_SET_ORDER);
        foreach($kvs as $kv) {
            $out['options'][urldecode($kv[1])] = urldecode($kv[2]);
        }
    }

    return $out;
}

/**
 * Parses a connection string and ensures its normalized.
 *
 * @param $dsn A connection string.
 * @return array
 *
 * @internal
 */
function cbdsn_parse($dsn) {
    return _cbdsn_normalize(_cbdsn_parse($dsn));
}

/**
 * Converts a connstr object to a connection string.
 *
 * @param $dsnObj
 * @return string
 *
 * @internal
 */
function _cbdsn_stringify($dsnObj) {
    $dsn = '';

    if ($dsnObj['scheme']) {
        $dsn .= $dsnObj['scheme'] . '://';
    }

    foreach($dsnObj['hosts'] as $i => $host) {
        if ($i !== 0) {
            $dsn .= ',';
        }
        $dsn .= $host[0];
        if ($host[1]) {
            $dsn .= ':' . $host[1];
        }
    }

    if ($dsnObj['bucket']) {
        $dsn .= '/' . $dsnObj['bucket'];
    }

    if ($dsnObj['options']) {
        $isFirstOption = true;
        foreach($dsnObj['options'] as $k => $v) {
            if ($isFirstOption) {
                $dsn .= '?';
                $isFirstOption = false;
            } else {
                $dsn .= '&';
            }
            $dsn .= urlencode($k) . '=' . urlencode($v);
        }
    }

    return $dsn;
}

/**
 * Ensures a connstr object is normalized then generates a connection string.
 *
 * @param $dsnObj
 * @return string
 *
 * @internal
 */
function cbdsn_stringify($dsnObj) {
    return _cbdsn_stringify(_cbdsn_normalize($dsnObj));
}