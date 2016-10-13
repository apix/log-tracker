<?php
/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log;

use Apix\Log\Emitter as LogEmitter;

class GoogleAnalyticsTest extends tests\TestCase
{
    protected $options = array('v' => 1, 'tid' => 'foo', 'cid' => 'bar');

    protected function setUp()
    {
        $this->logger = new GoogleAnalytics($this->options);
        $this->logger->getEmitter()->setDebug();
    }

    public function testConstructorUsesServerHttp()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'foo';
        $_SERVER['HTTP_REFERER'] = 'bar';

        $e = new LogEmitter\Async();
        $this->logger = new GoogleAnalytics($this->options, $e);

        $this->assertSame($_SERVER['HTTP_USER_AGENT'], $e->getParam('ua'));
        $this->assertSame($_SERVER['HTTP_REFERER'], $e->getParam('dr'));

        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsInvalidArgumentException()
    {
        new GoogleAnalytics(array());
    }

    public function testNonDeferredLogging()
    {
        $url = 'http://username:password@hostname:9090/path?arg=value#anchor';

        $exec = $this->grabMock()->expects($this->exactly(2));
        $exec->willReturnCallback(
            function ($cmd, &$output, &$return_var) use ($url) {
                $ns = urlencode(__NAMESPACE__);
                $url = urlencode($url);
                $this->assertEquals(
                    "curl -X POST -d 'v=1&tid=foo&cid=bar&ds={$ns}&t=pageview&dh=hostname&dp=%2Fpath&dt=title&dl={$url}' 'https://www.google-analytics.com/collect'",
                    $cmd
                );

                return true;
            }
        );
        $data = $this->logger->getPage($url, 'title');

        $this->logger->debug('track 1', $data);
        $this->logger->alert('track 2', $data);

        $this->assertAttributeEquals(
            GoogleAnalytics::TRACKER_URL_ONE, 'url',
            $this->logger->getEmitter()
        );
    }

    public function testDeferredLogging()
    {
        $exec = $this->grabMock()->expects($this->never());

        $this->logger->setDeferred(true);

        // Pageview
        $data = $this->logger->getPage('http://hostname/path', 'title');
        $this->logger->alert('page tracked', $data);

        // Event
        $data = $this->logger->getEvent('cat', 'action', 'label', 1);
        $this->logger->info('event tracked', $data);

        $this->assertCount(2, $this->logger->getDeferredLogs());

        $this->assertAttributeEquals(
            GoogleAnalytics::TRACKER_URL_MANY,
            'url', $this->logger->getEmitter());
    }

    /**
     * @group TODO
     */
    public function testDeferredLoggingWhileImplicitlyDestructing()
    {
        $max = 4; // number of logs

        $this->logger->setDeferred(true);

        foreach (range(1, $max) as $i) {
            $data = $this->logger->getEvent(
                'cat #.' . $i, 'action', 'label', $i
            );
            $this->logger->info('event #'.$i, $data);
        }
        $this->assertCount($max, $this->logger->getDeferredLogs());

        $exec = $this->grabMock()->expects($this->once());
        $exec->willReturnCallback(
            function ($cmd, &$output, &$return_var) use ($max) {
                $this->assertSame( $max-1, substr_count(
                    $cmd,
                    $this->logger->getLogFormatter()->separator
                ));
                return true;
            }
        );

        unset($this->logger); // call __destruct()
    }

    public function providerTrackerParams()
    {
        $base = $this->options + array('ds' => __NAMESPACE__);

        return array(
            array(
                'Page',
                array('ftp://foo.tld/path?stuff', 'title', 'location'),
                $base + array(
                    't' => 'pageview', 'dh' => 'foo.tld',
                    'dp' => '/path', 'dt' => 'title', 'dl' => 'location',
                ),
            ),
            array(
                'Event',
                array('category', 'action', 'label', '1.7'),
                $base + array(
                    't' => 'event', 'ec' => 'category', 'ea' => 'action',
                    'el' => 'label', 'ev' => 1,
                ),
            ),
            array(
                'Social',
                array('like', 'facebook', '/home'),
                $base + array(
                    't' => 'social', 'sa' => 'like',
                    'sn' => 'facebook', 'st' => '/home',
                ),
            ),
            array(
                'Exception',
                array('MyException', false),
                $base + array(
                    't' => 'exception', 'exd' => 'MyException', 'exf' => '0',
                ),
            ),
            array(
                'App',
                array('app name', '1.2.3', 'app id', 'app install id'),
                $base + array(
                    't' => 'screenview', 'an' => 'app name', 'av' => '1.2.3',
                    'aid' => 'app id', 'aiid' => 'app install id',
                ),
            ),
        );
    }

    /**
     * @dataProvider providerTrackerParams
     */
    public function testTrackingParams($method, $params, $exp)
    {
        $this->assertSame(
            $exp,
            call_user_func_array(array($this->logger, 'get'.$method), $params)
        );
    }
}
