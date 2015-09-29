<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;
use Cron\CronExpression;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class HttpCheckTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {

    }

    protected function _after()
    {
    }

    public function testGetNamespace()
    {
        $httpCheck = new HttpCheck(new DI());
        $this->assertNull($httpCheck->getNamespace(), 'HttpCheck::getNamespace null test failed!');

        $httpCheck = new HttpCheck(new DI(), '');
        $this->assertEquals('', $httpCheck->getNamespace(), 'HttpCheck::getNamespace empty string failed!');

        $httpCheck = new HttpCheck(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $httpCheck->getNamespace(), 'HttpCheck::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $httpCheck = new HttpCheck(new DI());
        $httpCheck->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $httpCheck->getNamespace(), 'HttpCheck::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $httpCheck = new HttpCheck(new DI());
        $this->assertNull($httpCheck->getCronExpression(), 'HttpCheck::getCronExpression null test failed!');

        $httpCheck = new HttpCheck(new DI(), '', '*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $httpCheck->getCronExpression(), 'HttpCheck::getCronExpression failed!');
    }

    public function testSetCronExpression()
    {
        $httpCheck = new HttpCheck(new DI(), '');
        $httpCheck->setCronExpression('*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $httpCheck->getCronExpression(), 'HttpCheck::setCronExpression failed!');
    }

    public function testCreateNewMetric()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('HttpCheck');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('2');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $httpCheck = new HttpCheck($diObj, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $httpCheck->createNewMetric('HttpCheck', 'Percent', '2'),
            'HttpCheck::createNewMetric test failed!'
        );
    }

    public function testSetUrl()
    {
        $httpCheck = new HttpCheck(new DI());
        $httpCheck->setUrl('https://github.com/fustundag/awscw-custom-metrics');
        $this->assertEquals(
            'https://github.com/fustundag/awscw-custom-metrics',
            $httpCheck->getUrl(),
            'HttpCheck::setUrl test failed!'
        );

        $httpCheck->setUrl('abc');
        $this->assertEquals(
            '',
            $httpCheck->getUrl(),
            'HttpCheck::setUrl malformed url test failed!'
        );
    }

    public function testGetUrl()
    {
        $httpCheck = new HttpCheck(new DI());
        $httpCheck->setUrl('https://github.com/fustundag/awscw-custom-metrics');
        $this->assertEquals(
            'https://github.com/fustundag/awscw-custom-metrics',
            $httpCheck->getUrl(),
            'HttpCheck::getUrl test failed!'
        );
    }

    public function testSetMethod()
    {
        $httpCheck = new HttpCheck(new DI());
        $httpCheck->setMethod('GET');
        $this->assertEquals('GET', $httpCheck->getMethod(), 'HttpCheck::setMethod test failed!');

        $httpCheck->setMethod('post');
        $this->assertEquals('POST', $httpCheck->getMethod(), 'HttpCheck::setMethod test failed!');

        $httpCheck->setMethod('abc');
        $this->assertEquals('', $httpCheck->getMethod(), 'HttpCheck::setMethod wrong method name test failed!');
    }

    public function testGetMethod()
    {
        $httpCheck = new HttpCheck(new DI());
        $this->assertEquals('GET', $httpCheck->getMethod(), 'HttpCheck::getMethod test failed!');
    }

    public function testGetHeaders()
    {
        $httpCheck = new HttpCheck(new DI());
        $this->assertCount(0, $httpCheck->getHeaders(), 'HttpCheck::getHeaders default value test failed!');

        $httpCheck->addHeader('header1', 'val1');
        $httpCheck->addHeader('header2', 'val2');

        $this->assertCount(2, $httpCheck->getHeaders(), 'HttpCheck::getHeaders test failed!');
        $this->assertArrayHasKey('header1', $httpCheck->getHeaders(), 'HttpCheck::getHeaders test failed!');
        $this->assertArrayHasKey('header2', $httpCheck->getHeaders(), 'HttpCheck::getHeaders test failed!');
        $this->assertContains('val1', $httpCheck->getHeaders(), 'HttpCheck::getHeaders test failed!');
        $this->assertContains('val2', $httpCheck->getHeaders(), 'HttpCheck::getHeaders test failed!');
    }

    public function testAddHeader()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->addHeader('header1', 'val1');
        $this->assertCount(1, $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');
        $this->assertArrayHasKey('header1', $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');
        $this->assertContains('val1', $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');

        $httpCheck->addHeader('header2', 'val2');
        $this->assertCount(2, $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');
        $this->assertArrayHasKey('header2', $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');
        $this->assertContains('val2', $httpCheck->getHeaders(), 'HttpCheck::addHeader test failed!');
    }

    public function testRemoveHeader()
    {
        $httpCheck = new HttpCheck(new DI());
        $httpCheck->addHeader('header1', 'val1');
        $httpCheck->addHeader('header2', 'val2');

        $httpCheck->removeHeader('header1');

        $this->assertCount(1, $httpCheck->getHeaders(), 'HttpCheck::removeHeader test failed!');
        $this->assertArrayNotHasKey('header1', $httpCheck->getHeaders(), 'HttpCheck::removeHeader test failed!');
        $this->assertArrayHasKey('header2', $httpCheck->getHeaders(), 'HttpCheck::removeHeader test failed!');
        $this->assertNotContains('val1', $httpCheck->getHeaders(), 'HttpCheck::removeHeader test failed!');
        $this->assertContains('val2', $httpCheck->getHeaders(), 'HttpCheck::removeHeader test failed!');
    }

    public function testSetTimeout()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->setTimeout(20);
        $this->assertEquals(20, $httpCheck->getTimeout(), 'HttpCheck::setTimeout test failed!');
    }

    public function testGetTimeout()
    {
        $httpCheck = new HttpCheck(new DI());

        $this->assertEquals(30, $httpCheck->getTimeout(), 'HttpCheck::getTimeout default value test failed!');

        $httpCheck->setTimeout(20);
        $this->assertEquals(20, $httpCheck->getTimeout(), 'HttpCheck::getTimeout test failed!');
    }

    public function testSetStatusToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->setStatusToCheck('500');
        $this->assertEquals('500', $httpCheck->getStatusToCheck(), 'HttpCheck::setStatusToCheck test failed!');
    }

    public function testGetStatusToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $this->assertEquals(
            '200',
            $httpCheck->getStatusToCheck(),
            'HttpCheck::getStatusToCheck default value test failed!'
        );

        $httpCheck->setStatusToCheck('400');
        $this->assertEquals('400', $httpCheck->getStatusToCheck(), 'HttpCheck::getStatusToCheck test failed!');
    }

    public function testSetHeadersToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->setHeadersToCheck(['header1' => 'val1', 'header2' => 'val2']);
        $this->assertEquals(
            ['header1' => 'val1', 'header2' => 'val2'],
            $httpCheck->getHeadersToCheck(),
            'HttpCheck::setHeadersToCheck test failed!'
        );
    }

    public function testGetHeadersToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $this->assertNull(
            $httpCheck->getHeadersToCheck(),
            'HttpCheck::getHeadersToCheck default value test failed!'
        );

        $httpCheck->setHeadersToCheck(['header1' => 'val1', 'header2' => 'val2']);
        $this->assertEquals(
            ['header1' => 'val1', 'header2' => 'val2'],
            $httpCheck->getHeadersToCheck(),
            'HttpCheck::setHeadersToCheck test failed!'
        );
    }

    public function testSetBodyCheckFunc()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->setBodyCheckFunc(HttpCheck::$containsFunc);
        $this->assertEquals(
            HttpCheck::$containsFunc,
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::setBodyCheckFunc test failed!'
        );

        $httpCheck->setBodyCheckFunc(
            function () {
                return 1==2;
            }
        );
        $this->assertEquals(
            function () {
                return 1==2;
            },
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::setBodyCheckFunc test2 failed!'
        );
    }

    public function testGetBodyCheckFunc()
    {
        $httpCheck = new HttpCheck(new DI());

        $this->assertNull(
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::getBodyCheckFunc default value test failed!'
        );

        $httpCheck->setBodyCheckFunc(
            function () {
                return 1==2;
            }
        );
        $this->assertEquals(
            function () {
                return 1==2;
            },
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::getBodyCheckFunc test failed!'
        );
    }

    public function testSetBodyToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $httpCheck->setBodyToCheck('body');
        $this->assertEquals(
            'body',
            $httpCheck->getBodyToCheck(),
            'HttpCheck::setBodyToCheck test failed!'
        );
        $this->assertEquals(
            HttpCheck::$equalsFunc,
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::setBodyToCheck body check func test failed!'
        );

        $httpCheck->setBodyToCheck('body2', HttpCheck::$containsFunc);
        $this->assertEquals(
            'body2',
            $httpCheck->getBodyToCheck(),
            'HttpCheck::setBodyToCheck test2 failed!'
        );
        $this->assertEquals(
            HttpCheck::$containsFunc,
            $httpCheck->getBodyCheckFunc(),
            'HttpCheck::setBodyToCheck body check func test2 failed!'
        );
    }

    public function testGetBodyToCheck()
    {
        $httpCheck = new HttpCheck(new DI());

        $this->assertNull(
            $httpCheck->getBodyToCheck(),
            'HttpCheck::getBodyToCheck default value test failed!'
        );

        $httpCheck->setBodyToCheck('body');
        $this->assertEquals(
            'body',
            $httpCheck->getBodyToCheck(),
            'HttpCheck::getBodyToCheck test failed!'
        );
    }

    public function testGetMetricsReturnFalse()
    {
        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());

        $httpCheck = new HttpCheck($diObj);
        $this->expectOutputString(
            "[".date('Y-m-d H:i:s')."][ERROR] Url is not defined to check!\n"
        );
        $returnArray = $httpCheck->getMetrics();
        $this->assertFalse($returnArray, 'HttpCheck::getMetrics return false failed!');

        $this->expectOutputString(
            "[".date('Y-m-d H:i:s')."][ERROR] Url is not defined to check!\n"
            ."[".date('Y-m-d H:i:s')."][ERROR] Method is not defined to check!\n"
        );
        $httpCheck->setUrl('https://github.com/fustundag/awscw-custom-metrics');
        $httpCheck->setMethod('abc');
        $returnArray = $httpCheck->getMetrics();
        $this->assertFalse($returnArray, 'HttpCheck::getMetrics return false failed!');
    }

    public function testGetMetrics()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('HttpCheck');
        $expectedMetric->setUnit('Seconds');
        $expectedMetric->setValue('0');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $expectedFailMetric = new Metric();
        $expectedFailMetric->setName('HttpCheckFail');
        $expectedFailMetric->setUnit('Count');
        $expectedFailMetric->setValue('1');
        $expectedFailMetric->setNamespace('CustomMetric/Test');

        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], 'response body'),
            new Response(500, ['Content-Type' => 'application/json'], 'response failed!'),
            new Response(200, ['Content-Type' => 'application/json'], 'response OK'),
            new Response(200, ['Content-Type' => 'application/json'], 'response FAIL'),
            new Response(200, ['Content-Type' => 'application/json', 'Content-Length' => 11], 'response OK'),
            new Response(200, ['Content-Type' => 'application/json', 'Content-Length' => 11], 'response OK'),
            new Response(200, ['Content-Type' => 'application/json', 'Content-Length' => 11], 'response OK'),
            new TransferException('Transfer failed', 500),
            new Response(
                200,
                ['Content-Type' => 'application/json', 'Content-Length' => 11],
                'amount=0.0&cmd=SALE&status=-10220&subscriptionType=&batchID=1443429052723'
                .'&part=905365853383&txnID=1443429052724'
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json', 'Content-Length' => 11],
                'amount=0.0&cmd=SALE&status=-10220&subscriptionType=&batchID=1443429052723'
                .'&part=905365853383&txnID=1443429052724'
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json', 'X-Header-Exists' => 'random value'],
                'response OK'
            ),
        ]);
        $handler = HandlerStack::create($mock);
        $client  = new Client(['handler' => $handler]);
        $diObj->setGuzzleHttpClient($client);

        $httpCheck = new HttpCheck($diObj, 'CustomMetric/Test');

        $httpCheck->setUrl('https://github.com/fustundag/awscw-custom-metrics');

        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'HttpCheck::getMetrics expected metric test failed!');
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'HttpCheck::getMetrics check status test failed!');


        $httpCheck->setBodyToCheck('response OK');

        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'HttpCheck::getMetrics body check ok test failed!');
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'HttpCheck::getMetrics body check fail test failed!');

        $httpCheck->setHeadersToCheck(['Content-Type' => 'application/json']);
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'HttpCheck::getMetrics headers check ok test failed!');

        $httpCheck->setHeadersToCheck(['Content-Type' => 'text/html']);
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'HttpCheck::getMetrics headers check fail test failed!');

        $httpCheck->setHeadersToCheck(['Non-Exists' => 'foobar']);
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'HttpCheck::getMetrics headers check fail test failed!');

        $this->expectOutputString(
            "[".date('Y-m-d H:i:s')."][ERROR] Guzzle Http client thrown exception! Msg: Transfer failed\n"
        );
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics exception test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'HttpCheck::getMetrics exception test failed!');

        $httpCheck->setHeadersToCheck([]);
        $httpCheck->setBodyToCheck('status=-10220', HttpCheck::$containsFunc);
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'HttpCheck::getMetrics body contains test failed!');

        $httpCheck->setBodyToCheck('amount=0.0', HttpCheck::$containsFunc);
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals(
            $expectedMetric,
            $metrics[0],
            'HttpCheck::getMetrics body contains at the beginning test failed!'
        );

        $httpCheck->setHeadersToCheck(['X-Header-Exists' => '']);
        $httpCheck->setBodyToCheck('');
        $metrics = $httpCheck->getMetrics();
        $this->assertCount(1, $metrics, 'HttpCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'HttpCheck::getMetrics headers exists test failed!');

    }
}
