<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\LogFormatter;

use Apix\Log\LogFormatter;
use Apix\Log\LogEntry;

/**
 * Json log formatter for Apix log.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Json extends LogFormatter
{
    /**
     * Formats the given log entry.
     *
     * @param LogEntry $log The log entry to format
     *
     * @return string
     */
    public function format(LogEntry $log)
    {
        return json_encode($log->context);
    }
}
