<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Emitter;

/**
 * Log Emitter Interface.
 *
 * To contribute a log emmiter, essentially it needs to:
 *    - Extends the `AbstractEmitter`
 *    - Implements this interface `EmitterInterface`
 *
 * @example
 *   class StandardOutput extends AbstractEmitter implements EmitterInterface
 *   {
 *     public function send($payload)
 *     {
 *         return strtolower($playload);
 *     }
 *   }
 *
 * @see tests/EmitterInterfaceTest.php     For a more detailed example
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
interface EmitterInterface
{
    /**
     * Sends the given payload.
     *
     * @param string $payload The data payload string to send
     *
     * @return bool Indicates the status of the emmission
     */
    public function send($payload);
}
