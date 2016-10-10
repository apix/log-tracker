<?php

namespace Apix\Log\Emitter;

use Psr\Log\InvalidArgumentException;

class Async extends AbstractEmitter
{
    /**
     * Holds the emitter/transporter's command string.
     * The placeholders in order are:
     *      - #1.    (string)    payload
     *      - #2.    (string)    url.
     */
    protected $transporter_cmd = 'curl -X POST -d %1$s \'%2$s\'';

    /**
     * Constructor.
     *
     * @param string $transporter_cmd The tranpsort command string
     */
    public function __construct($transporter_cmd = null)
    {
        $this->transporter_cmd = $transporter_cmd ?: $this->transporter_cmd;
    }

    /**
     * {@inheritdoc}
     */
    public function send($payload)
    {
        if (!is_string($payload)) {
            throw new InvalidArgumentException(sprintf(
                'Expects a string, got: (%s) %s.',
                gettype($payload), json_encode($payload)
            ));
        }

        $cmd = sprintf(
            $this->transporter_cmd,
            escapeshellarg($payload),
            $this->url // urlencode($this->url)
        );

        if (!$this->debug) {
            $cmd .= ' > /dev/null 2>&1 &';
            // $cmd .= ' >&- 2>&- &';
        }

        exec($cmd, $output, $return_var);

        if ($return_var != 0) {
            $this->handleError($return_var, $output);
        }

        return $return_var == 0;
    }
}
