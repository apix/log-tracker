<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

class AbstractTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateUuid()
    {
        $this->assertRegExp(
            '/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/',
            AbstractTracker::generateUuid());
    }
}
