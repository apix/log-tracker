<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\Emitter;

use phpmock\phpunit\PHPMock;

class AsyncTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    protected $emitter;

    protected function setUp()
    {
        $this->emitter = new Async('cmd');
    }

    protected function tearDown()
    {
        unset($this->emitter);
    }

    public function testSend()
    {
        $exec = $this->getFunctionMock(__NAMESPACE__, 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($cmd, &$output, &$return_var) {
                $this->assertEquals('cmd > /dev/null 2>&1 &', $cmd);
            }
        );

        $payload = 'foo&bar';
        $this->assertSame(
            true, // succesful
            $this->emitter->send($payload)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSendWillThrowsInvalidArgumentException()
    {
        $this->emitter->send(array());
    }

    public function testDefaultSendHandleError()
    {
        $err = $this->getFunctionMock('\Apix\Log\Logger', 'error_log');
        $err->expects($this->once())->willReturnCallback(
            function ($msg) {
                echo $msg;
            }
        );

        $exec = $this->getFunctionMock(__NAMESPACE__, 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($cmd, &$exit_msg, &$exit_code) {
                $exit_msg = 'an error msg';
                $exit_code = 123;
            }
        );

        $this->emitter->send('foo');
        $this->expectOutputRegex('/\] ERROR \w.* - an error msg - Code #123$/');
    }

    public function testSetErrorHandler()
    {
        $logger = new \Apix\Log\Logger\Nil;
        $this->emitter->setErrorHandler( $logger );
        $this->assertSame($logger, $this->emitter->getErrorHandler());
    }

    public function testSetterGetterParams()
    {
        $this->assertFalse($this->emitter->hasParam('foo'));
        $this->emitter->setParam('foo', 'bar');
        $this->assertTrue($this->emitter->hasParam('foo'));
        $this->assertSame('bar', $this->emitter->getParam('foo'));
    }
}