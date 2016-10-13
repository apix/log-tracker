<?php

namespace Apix\Log\Emitter;

use Apix\Log\Logger;
use Apix\Log\Logger\LoggerInterface;

abstract class AbstractEmitter implements EmitterInterface
{
    /**
     * Holds the emitter/transporter's URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Holds the emitter/transporter's debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Holds the query parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * Sets the emitter/transporter's URL.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = vsprintf($url, $this->params);
    }

    /**
     * Sets the emitter/transporter's debug mode.
     *
     * @param bool $bool
     */
    public function setDebug($bool = true)
    {
        $this->debug = (bool) $bool;
    }

    /**
     * Returns the current query parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Checks wether the named query parameter exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Returns the named query parameter.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getParam($key)
    {
        return $this->params[$key];
    }

    /**
     * Sets a query parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Merges new query parameters.
     *
     * @param array $params
     *
     * @return array
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Merges new query parameters.
     *
     * @param array $params
     *
     * @return array
     */
    public function addParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    /**
     * @var Logger
     */
    protected $error_handler = null;

    public function setErrorHandler(LoggerInterface $logger)
    {
        $this->error_handler = $logger;
    }

    /**
     * Returns the.
     */
    public function getErrorHandler()
    {
        $this->error_handler = $this->error_handler ?: new Logger\ErrorLog('/dev/stdout');

        return $this->error_handler;
    }

    /**
     * Handle error.
     *
     * @param string $code
     * @param string $msg
     */
    public function handleError($code, $msg)
    {
        $logger = $this->getErrorHandler();
        $logger->error(
            '{0} - {1} - Code #{2}',
            array(get_class($this), $msg, $code)
        );
    }
}
