<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;

class RedisCheckTest extends \Codeception\TestCase\Test
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
        $obj = new RedisCheck(new DI());
        $this->assertNull($obj->getNamespace(), 'RedisCheck::getNamespace null test failed!');

        $obj = new RedisCheck(new DI(), '');
        $this->assertEquals('', $obj->getNamespace(), 'RedisCheck::getNamespace empty string failed!');

        $obj = new RedisCheck(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $obj->getNamespace(), 'RedisCheck::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $obj = new RedisCheck(new DI());
        $obj->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $obj->getNamespace(), 'RedisCheck::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $obj = new RedisCheck(new DI());
        $this->assertNull($obj->getCronExpression(), 'RedisCheck::getCronExpression null test failed!');

        $obj = new RedisCheck(new DI(), '', '*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $obj->getCronExpression(), 'RedisCheck::getCronExpression failed!');
    }

    public function testSetCronExpression()
    {
        $obj = new RedisCheck(new DI(), '');
        $obj->setCronExpression('*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $obj->getCronExpression(), 'RedisCheck::setCronExpression failed!');
    }

    public function testSetServer()
    {
        $obj = new RedisCheck(new DI(), '');
        $obj->setServer('myserver');
        $this->assertEquals('myserver', $obj->getServer(), 'RedisCheck::setServer failed!');
    }

    public function testGetServer()
    {
        $obj = new RedisCheck(new DI());
        $this->assertEquals('localhost', $obj->getServer(), 'RedisCheck::getServer default test failed!');
    }

    public function testSetPort()
    {
        $obj = new RedisCheck(new DI(), '');
        $obj->setPort('1234');
        $this->assertEquals('1234', $obj->getPort(), 'RedisCheck::setPort failed!');
    }

    public function testGetPort()
    {
        $obj = new RedisCheck(new DI());
        $this->assertEquals('6379', $obj->getPort(), 'RedisCheck::getPort default test failed!');
    }

    public function testSetKeys()
    {
        $obj = new RedisCheck(new DI(), '');
        $obj->setKeys(['1','2']);
        $this->assertEquals(['1','2'], $obj->getKeys(), 'RedisCheck::setKeys failed!');
    }

    public function testGetKeys()
    {
        $obj = new RedisCheck(new DI());
        $this->assertEquals([], $obj->getKeys(), 'RedisCheck::getKeys default test failed!');
    }

    public function testGetMetricsNoKeys()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('RedisKeysLen');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('0');
        $expectedMetric->setNamespace('CustomMetric/Test');

        /* @var CommandRunner $fakeCmdRunner */
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function () {
            },
            'getReturnCode' => 0,
            'getOutput' => [0]
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);

        $redisCheck = new RedisCheck($diObj, 'CustomMetric/Test');
        $returnArray = $redisCheck->getMetrics();
        $this->assertCount(1, $returnArray, 'RedisCheck return array failed');
        $this->assertEquals(
            $expectedMetric,
            $returnArray[0],
            'RedisCheck return object failed!'
        );
    }

    public function testGetMetricsWithFindKeys()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('RedisKeysLen');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('30');
        $expectedMetric->setNamespace('CustomMetric/Test');

        /* @var CommandRunner $fakeCmdRunner */
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function ($cmd) {
            },
            'getOutput' => Stub::consecutive([2], ['key1','key2'], [14], [16]),
            'getReturnCode' => 0
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);

        $redisCheck  = new RedisCheck($diObj, 'CustomMetric/Test');
        $returnArray = $redisCheck->getMetrics();
        $this->assertCount(1, $returnArray, 'RedisCheck return array failed');
        $this->assertEquals(
            $expectedMetric,
            $returnArray[0],
            'RedisCheck return object failed!'
        );
    }

    public function testGetMetricsWithOneKey()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('RedisKeysLen');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('15');
        $expectedMetric->setNamespace('CustomMetric/Test');

        /* @var CommandRunner $fakeCmdRunner */
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function () {
            },
            'getReturnCode' => 0,
            'getOutput' => Stub::consecutive([15])
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);

        $redisCheck = new RedisCheck($diObj, 'CustomMetric/Test');
        $redisCheck->setKeys(['key1']);
        $returnArray = $redisCheck->getMetrics();
        $this->assertCount(1, $returnArray, 'RedisCheck return array failed');
        $this->assertEquals(
            $expectedMetric,
            $returnArray[0],
            'RedisCheck return object failed!'
        );
    }
}
