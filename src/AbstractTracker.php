<?php

namespace Apix\Log;

use Apix\Log\Emitter\EmitterInterface as LogEmitter;
use Ramsey\Uuid\Uuid;

/**
 * Abstract tracking logger for Apix Log.
 */
abstract class AbstractTracker extends Logger\AbstractLogger implements Logger\LoggerInterface
{

    /**
     * Holds a LogEmitter instance.
     *
     * @var LogEmitter
     */
    protected $emitter;

    /**
     * Holds the current UUID (currently only v4).
     *
     * @var string
     */
    protected $uuid;

    /**
     * Returns the log emmiter.
     *
     * @return LogEmitter
     */
    public function setEmitter(LogEmitter $emitter, LogFormatter $formatter)
    {
        $this->emitter = $emitter;
        $this->setLogFormatter($formatter);
    }

    /**
     * Returns the log emmiter.
     *
     * @return LogEmitter
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * {@inheritdoc}
     */
    public function write(LogEntry $log)
    {
        try {
            $payload = $this->deferred ? $log->message : $log;
            return $this->emitter->send((string)$payload);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Generates an UUID (currently only v4).
     *
     * @https://pecl.php.net/package/uuid
     *
     * @return string
     */
    public static function generateUuid()
    {
        return function_exists('uuid_create')
                ? uuid_create(\UUID_TYPE_DEFAULT) // PECL extension is faster.
                : Uuid::uuid4()->toString();
    }

    /**
     * Returns the current UUID.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
