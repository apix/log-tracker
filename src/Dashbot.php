<?php

namespace Apix\Log;

use Apix\Log\Emitter\EmitterInterface as LogEmitter;
use Psr\Log\InvalidArgumentException;

/**
 * Dashbot logger for Apix Log.
 *
 * @see https://www.dashbot.io/sdk/generic
 */
class Dashbot extends AbstractTracker
{
    const TRACKER_URL =
        'https://tracker.dashbot.io/track?platform=%s&v=%s&type=%s&apiKey=%s';

    const TRANSPORTER_CMD =
        'curl -X POST -d %1$s \'%2$s\' -H \'Content-Type: application/json\'';

    const DEFAULT_PARAMS = array(
        'platform' => 'generic',    // Either generic, facebook, slack, kik
        'v' => '0.7.4-rest',        // API Version
        'type' => null,             // Hit type (required)
        'apiKey' => null,           // API key (required)
    );

    /**
     * Constructor.
     *
     * @param
     */
    public function __construct(
        $mixed, LogEmitter $emitter = null, LogFormatter $formatter = null
    ) {
        $this->setEmitter(
            $emitter ? $emitter : new Emitter\Async(self::TRANSPORTER_CMD),
            $formatter ? $formatter : new LogFormatter\Json()
        );
        $this->emitter->setParams(self::DEFAULT_PARAMS);

        if (is_array($mixed) && isset($mixed['apiKey'])) {
            $this->emitter->addParams($mixed);
        } elseif (is_string($mixed)) {
            $this->emitter->setParam('apiKey', $mixed);
        } else {
            throw new InvalidArgumentException(sprintf(
                '%s expects `apiKey` to be set, got: %s.',
                __CLASS__, json_encode($mixed)
            ));
        }
    }

    /**
     * Sets the platform (format).
     *
     * @see https://www.dashbot.io/sdk/template
     *
     * @param string $platform Either 'generic', 'facebook', 'slack', 'kik'
     *
     * @return self
     */
    public function setPlatform($platform)
    {
        $this->emitter->setParam('platform', $platform);

        return $this;
    }

    /**
     * Sets a global tag (used to combined metrics).
     *
     * @see https://www.dashbot.io/sdk/template
     *
     * @param array  $entries
     * @param string $local_tag
     *
     * @return self
     */
    public function setGlobalTag($global_tag = null)
    {
        $this->emitter->setParam('dashbotTemplateId', $global_tag);

        return $this;
    }

    /**
     * Rewrite the dashbot template Id.
     *
     * @see https://www.dashbot.io/sdk/template
     *
     * @param array $entries
     * @param array $params
     *
     * @return array
     */
    public function rewriteTemplateId(array $entries, array $params)
    {
        if (isset($entries['json']) && $params['platform'] == 'facebook') {
            if ($tpl_id = // get from `entries` (local) then `params` (global).
                 $entries['dashbotTemplateId'] ?: $params['dashbotTemplateId']
                    ?: false
            ) {
                $entries['json']['dashbotTemplateId'] = $tpl_id;
                // remove from `entries` (local) to avoid duplicate.
                unset($entries['dashbotTemplateId']);
            }
        }

        return $entries;
    }

    /**
     * Sets the event hit type.
     *
     * @param string $type
     * @param array  $entries
     * @param string $local_tag
     *
     * @return array
     */
    protected function get($type, array $entries, $local_tag = null)
    {
        if ($local_tag) {
            $entries['dashbotTemplateId'] = $local_tag;
        }

        $entries = $this->rewriteTemplateId(
            $entries, $this->emitter->getParams()
        );

        $this->emitter->setParam('type', $type);
        $this->emitter->setUrl(self::TRACKER_URL);

        return (array) $entries;
    }

    /**
     * Returns an incoming tracking dataset.
     *
     * @param array  $entries
     * @param string $local_tag
     *
     * @return array
     */
    public function incoming(array $entries, $local_tag = null)
    {
        return $this->get(__FUNCTION__, $entries, $local_tag);
    }

    /**
     * Returns an outgoing tracking dataset.
     *
     * @param array  $entries
     * @param string $local_tag
     *
     * @return array
     */
    public function outgoing(array $entries, $local_tag = null)
    {
        return $this->get(__FUNCTION__, $entries, $local_tag);
    }
}
