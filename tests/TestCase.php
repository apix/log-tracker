<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests;

use phpmock\phpunit\PHPMock;

class TestCase extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    protected $logger;

    protected function tearDown()
    {
        unset($this->logger);
    }

    protected function grabMock($func = 'exec', $class = '\Apix\Log\Emitter')
    {
        return $this->getFunctionMock($class, $func);
    }

    /**
     * @expectedException \Apix\Log\Exception
     */
    public function testWriteCatchException()
    {
        $exec = $this->grabMock()->expects($this->once());
        $exec->will($this->throwException(new \Exception()));

        $this->logger->error('foo', array());
    }
}
