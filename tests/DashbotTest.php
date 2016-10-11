<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

class DashbotTest extends tests\TestCase
{
    protected $options = array('apiKey' => 'foo', 'v' => 123);

    protected function setUp()
    {
        $this->logger = new Dashbot($this->options);
        $this->logger->getEmitter()->setDebug();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsInvalidArgumentException()
    {
        new Dashbot(array());
    }

    public function testFunctionalIncomingLogging()
    {
        $exec = $this->grabMock()->expects($this->once());
        $exec->willReturnCallback(
            function ($cmd, &$output, &$return_var) {
                // echo $cmd;
                $this->assertEquals(
                    "curl -X POST -d '[\"msg\"]' 'https://tracker.dashbot.io/track?platform=generic&v=123&type=incoming&apiKey=foo' -H 'Content-Type: application/json'",
                    $cmd
                );

                return true;
            }
        );

        $data = $this->logger->incoming(array('msg'));
        $this->logger->error('track incoming', $data);
    }

    public function testFunctionalOutgoingLogging()
    {
        $exec = $this->grabMock()->expects($this->once());
        $exec->willReturnCallback(
            function ($cmd, &$output, &$return_var) {
                // echo $cmd;
                $this->assertEquals(
                    "curl -X POST -d '[\"msg\"]' 'https://tracker.dashbot.io/track?platform=generic&v=123&type=outgoing&apiKey=foo' -H 'Content-Type: application/json'",
                    $cmd
                );

                return true;
            }
        );

        $data = $this->logger->outgoing(array('msg'));
        $this->logger->error('track outgoing', $data);
    }

    protected function _getParams()
    {
        return $this->logger->getEmitter()->getParams();
    }

    public function testConstructor()
    {
        $params = $this->_getParams();

        $this->assertSame('foo', $params['apiKey']);
        $this->assertNull($params['type']);
    }

    public function testConstructorWithAnArrayOfParams()
    {
        $this->logger = new Dashbot($this->options);

        $this->assertEquals(
            array(
                'platform' => 'generic',
                'v' => 123,
                'type' => null,
                'apiKey' => 'foo',
            ),
            $this->_getParams()
        );
    }

    public function testConstructorWithOneArg()
    {
        $this->logger = new Dashbot('boo');
        $params = $this->_getParams();

        $this->assertSame('boo', $params['apiKey']);
    }

    public function providerTrackerParams()
    {
        $arr = array('foo' => 'bar');

        // simple
        $tests = array(
            array('incoming', array($arr), $arr),
            array('outgoing', array($arr), $arr),
        );

        // with dashbotTemplateId
        $arr_tpl = $arr + array('dashbotTemplateId' => 'biz');
        $tests[] = array('incoming', array($arr_tpl), $arr_tpl);

        // `dashbotTemplateId` rewritten for Facebook
        $arr_json = $arr + array('dashbotTemplateId' => 'biz', 'json' => array());
        $arr_fb = $arr + array('json' => array('dashbotTemplateId' => 'biz'));
        $tests[] = array('incoming', array($arr_json), $arr_fb, 'facebook');

        // `dashbotTemplateId` rewritten for Facebook
        $arr_json = $arr + array('dashbotTemplateId' => 'biz', 'json' => array());
        $arr_fb = $arr + array('json' => array('dashbotTemplateId' => 'biz'));
        $tests[] = array('incoming', array($arr_json), $arr_fb, 'facebook2');

        return $tests;
    }

    /**
     * @dataProvider providerTrackerParams
     */
    public function testTracking($type, array $arr, array $exp, $p = 'generic')
    {
        if ($p == 'facebook2') {
            $this->logger = new Dashbot(
                $this->options +
                array('platform' => 'facebook', 'dashbotTemplateId' => 'biz')
            );
        } else {
            $this->logger->setPlatform($p);
        }

        $results = call_user_func_array(array($this->logger, $type), $arr);
        $this->assertSame($exp, $results);
        $params = $this->_getParams();
        $this->assertSame($type, $params['type']);
    }
}
