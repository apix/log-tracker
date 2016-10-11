<?php

namespace Apix\Log;

use Apix\Log\Emitter\EmitterInterface as LogEmitter;
use Psr\Log\InvalidArgumentException;

/*
    TODO:
    A maximum of 20 hits can be specified per request.
    The total size of all hit payloads cannot be greater than 16K bytes.
    No single hit payload can be greater than 8K bytes.
*/

/**
 * Google Analytics logger for Apix Log.
 *
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
 */
class GoogleAnalytics extends AbstractTracker
{
    const TRACKER_URL_ONE = 'https://www.google-analytics.com/collect';

    const TRACKER_URL_MANY = 'https://www.google-analytics.com/batch';

    //const DEFAULT_PARAMS = array(
    private $DEFAULT_PARAMS = array(
        'v' => 1,             // API Version
        'tid' => null,          // Tracking/Property (required) ID e.g. UA-XX-XX
        'cid' => null,          // Anonymous Client ID UUIDv4
                                // see http://www.ietf.org/rfc/rfc4122.txt
        'ds' => __NAMESPACE__, // Data Source
        't' => null,          // Hit type (required)
    );

    /**
     * Constructor.
     *
     * @param array $params Array of Google Analytics parameters
     */
    public function __construct(
        array $params, LogEmitter $emitter = null, LogFormatter $formatter = null
    ) {
        if (!isset($params['tid'])) {
            throw new InvalidArgumentException(sprintf(
                '%s expects `tid` to bet provided, got: %s.',
                __CLASS__, json_encode($params)
            ));
        }

        if (!isset($params['cid'])) {
            $params['cid'] = self::generateUuid();
        }
        $this->uuid = $params['cid'];

        $this->setemitter(
            $emitter ? $emitter : new Emitter\Async(),
            $formatter ? $formatter : new LogFormatter\QueryString()
        );
        $this->emitter->setParams($this->DEFAULT_PARAMS);

        if (isset($_SERVER['HTTP_USER_AGENT']) && !isset($params['ua'])) {
            $params['ua'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['HTTP_REFERER']) && !isset($params['dr'])) {
            $params['dr'] = $_SERVER['HTTP_REFERER'];
        }

        if (isset($_SERVER['REMOTE_ADDR']) && !isset($params['uip'])) {
            $params['uip'] = $_SERVER['REMOTE_ADDR'];
        }

        $this->emitter->addParams($params);
    }

    /**
     * Returns a Page Tracking dataset.
     *
     * @param string $url      The full URL for ht page document
     * @param string $title    The title of the page / document
     * @param string $location Document location URL
     *
     * @return array
     */
    public function getPage($url, $title = null, $location = null)
    {
        $params = array();

        if (0 != strpos($url, '/')) {
            $_ = parse_url($url);

            // Document hostname
            if (isset($_['host'])) {
                $params['dh'] = $_['host'];
            }

            // Page
            $params['dp'] = $_['path'];
        }

        // Page title
        if ($title) {
            $params['dt'] = $title;
        }

        // Document location URL
        $params['dl'] = $location ? $location : $url;

        return $this->get('pageview', $params);
    }

    /**
     * Returns an Event Tracking dataset.
     *
     * @param string $category
     * @param string $action
     * @param string $label
     * @param string $value
     *
     * @return array
     */
    public function getEvent($category, $action, $label = null, $value = null)
    {
        $params = array(
            'ec' => $category,  // Event Category. Required.
            'ea' => $action,    // Event Action. Required.
        );

        // Event label
        if ($label) {
            $params['el'] = (string) $label;
        }

        // Event value
        if ($value) {
            $params['ev'] = (int) $value; // GA does not allow float!
        }

        return $this->get('event', $params);
    }

    /**
     * Returns a Social Interactions dataset.
     *
     * @param string $action Social Action (e.g. like)
     * @param string $label  Social Network (e.g. facebook)
     * @param string $value  Social Target. (e.g. /home)
     *
     * @return array
     */
    public function getSocial($action, $network, $target)
    {
        $params = array(
            'sa' => (string) $action,
            'sn' => (string) $network,
            'st' => (string) $target,
        );

        return $this->get('social', $params);
    }

    /**
     * Returns an Exception Tracking dataset.
     *
     * @param string $description Exception description
     * @param string $isFatal     Specifies whether the exception was fatal
     *
     * @return array
     */
    public function getException($description, $isFatal = true)
    {
        $params = array(
            'exd' => (string) $description,
            'exf' => $isFatal ? '1' : '0',
        );

        return $this->get('exception', $params);
    }

    /**
     * Returns an App / Screen Tracking dataset.
     *
     * @param string $name    App name
     * @param string $version App version
     * @param string $id      App Id
     * @param string $iid     App Installer Id
     *
     * @return array
     */
    public function getApp($name, $version = null, $id = null, $iid = null)
    {
        $params = array(
            'an' => (string) $name,
            'av' => (string) $version,
            'aid' => (string) $id,
            'aiid' => (string) $iid,
        );

        return $this->get('screenview', $params);
    }

    /**
     * Returns the named tracking dataset.
     *
     * @return array
     */
    public function get($type, array $params)
    {
        $this->emitter->setParam('t', $type);
        $this->emitter->setUrl(
            $this->deferred ? self::TRACKER_URL_MANY : self::TRACKER_URL_ONE
        );

        return array_merge($this->emitter->getParams(), $params);
    }
}
