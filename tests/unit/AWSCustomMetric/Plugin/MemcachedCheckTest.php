<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;

class MemcachedCheckTest extends \Codeception\TestCase\Test
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
        $memcachedCheck = new MemcachedCheck(new DI());
        $this->assertNull($memcachedCheck->getNamespace(), 'MemcachedCheck::getNamespace null test failed!');

        $memcachedCheck = new MemcachedCheck(new DI(), '');
        $this->assertEquals('', $memcachedCheck->getNamespace(), 'MemcachedCheck::getNamespace empty string failed!');

        $memcachedCheck = new MemcachedCheck(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $memcachedCheck->getNamespace(), 'MemcachedCheck::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $memcachedCheck = new MemcachedCheck(new DI());
        $memcachedCheck->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $memcachedCheck->getNamespace(), 'MemcachedCheck::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $memcachedCheck = new MemcachedCheck(new DI());
        $this->assertNull($memcachedCheck->getCronExpression(), 'MemcachedCheck::getCronExpression null test failed!');

        $memcachedCheck = new MemcachedCheck(new DI(), '', '*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $memcachedCheck->getCronExpression(),
            'MemcachedCheck::getCronExpression failed!'
        );
    }

    public function testSetCronExpression()
    {
        $memcachedCheck = new MemcachedCheck(new DI(), '');
        $memcachedCheck->setCronExpression('*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $memcachedCheck->getCronExpression(),
            'MemcachedCheck::setCronExpression failed!'
        );
    }

    public function testCreateNewMetric()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('MemcachedCheck');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('2');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $memcachedCheck = new MemcachedCheck($diObj, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $memcachedCheck->createNewMetric('MemcachedCheck', 'Percent', '2'),
            'MemcachedCheck::createNewMetric test failed!'
        );
    }

    public function testSetServer()
    {
        $checkObj = new MemcachedCheck(new DI());
        $checkObj->setServer('127.0.0.1');
        $this->assertEquals(
            '127.0.0.1',
            $checkObj->getServer(),
            'MemcachedCheck::setServer test failed!'
        );
    }

    public function testSetPort()
    {
        $checkObj = new MemcachedCheck(new DI());
        $checkObj->setPort('11211');
        $this->assertEquals(
            '11211',
            $checkObj->getPort(),
            'MemcachedCheck::setPort test failed!'
        );
    }

    public function testSetMemcached()
    {
        $memcached = new \Memcached();
        $checkObj  = new MemcachedCheck(new DI());
        $checkObj->setMemcached($memcached);
        $this->assertEquals(
            $memcached,
            $checkObj->getMemcached(),
            'MemcachedCheck::setMemcached test failed!'
        );
    }

    public function testGetMetrics()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('MemcachedCheckFail');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('0');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $expectedFailMetric = new Metric();
        $expectedFailMetric->setName('MemcachedCheckFail');
        $expectedFailMetric->setUnit('Count');
        $expectedFailMetric->setValue('1');
        $expectedFailMetric->setNamespace('CustomMetric/Test');

        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());

        $memcachedCheck = new MemcachedCheck($diObj, 'CustomMetric/Test');

        $memcachedCheck->setServer('127.0.0.1');
        $memcachedCheck->setPort('11211');
        $memcachedCheck->setMemcached(new \Memcached());

        $metrics = $memcachedCheck->getMetrics();
        $this->assertCount(1, $metrics, 'MemcachedCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'MemcachedCheck::getMetrics expected metric test failed!');

        $memcachedCheck->setServer('127.0.0.1');
        $memcachedCheck->setPort('11221');
        $metrics = $memcachedCheck->getMetrics();
        $this->assertCount(1, $metrics, 'MemcachedCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'MemcachedCheck::getMetrics check status test failed!');

        $fakeMemcached = Stub::make('\Memcached', [
            'addServer' => function () {
            },
            'resetServerList' => function () {
            },
            'set' => function () {
                return true;
            },
            'get'  => 2
        ]);
        $memcachedCheck->setMemcached($fakeMemcached);
        $metrics = $memcachedCheck->getMetrics();
        $this->assertCount(1, $metrics, 'MemcachedCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'MemcachedCheck::getMetrics check status test failed!');

        $fakeMemcached = Stub::make('\Memcached', [
            'addServer' => function () {
            },
            'resetServerList' => function () {
            },
            'set' => function () {
                throw new \Exception('fake exception on memcached set');
            },
            'get'  => 2
        ]);
        $memcachedCheck->setMemcached($fakeMemcached);
        $metrics = $memcachedCheck->getMetrics();
        $this->assertCount(1, $metrics, 'MemcachedCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'MemcachedCheck::getMetrics check status test failed!');
    }
}
