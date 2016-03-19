<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;

class ServicePortCheckTest extends \Codeception\TestCase\Test
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
        $servicePortCheck = new ServicePortCheck(new DI());
        $this->assertNull($servicePortCheck->getNamespace(), 'ServicePortCheck::getNamespace null test failed!');

        $servicePortCheck = new ServicePortCheck(new DI(), '');
        $this->assertEquals(
            '',
            $servicePortCheck->getNamespace(),
            'ServicePortCheck::getNamespace empty string failed!'
        );

        $servicePortCheck = new ServicePortCheck(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $servicePortCheck->getNamespace(), 'ServicePortCheck::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $servicePortCheck = new ServicePortCheck(new DI());
        $servicePortCheck->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $servicePortCheck->getNamespace(), 'ServicePortCheck::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $servicePortCheck = new ServicePortCheck(new DI());
        $this->assertNull(
            $servicePortCheck->getCronExpression(),
            'ServicePortCheck::getCronExpression null test failed!'
        );

        $servicePortCheck = new ServicePortCheck(new DI(), '', '*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $servicePortCheck->getCronExpression(),
            'ServicePortCheck::getCronExpression failed!'
        );
    }

    public function testSetCronExpression()
    {
        $servicePortCheck = new ServicePortCheck(new DI());
        $servicePortCheck->setCronExpression('*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $servicePortCheck->getCronExpression(),
            'ServicePortCheck::setCronExpression failed!'
        );
    }

    public function testCreateNewMetric()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('ServicePortCheck');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('2');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $servicePortCheck = new ServicePortCheck($diObj, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $servicePortCheck->createNewMetric('ServicePortCheck', 'Count', '2'),
            'ServicePortCheck::createNewMetric test failed!'
        );
    }

    public function testSetServiceName()
    {
        $checkObj = new ServicePortCheck(new DI());
        $checkObj->setServer('127.0.0.1');
        $checkObj->setPort('123456');
        $this->assertEquals(
            '127.0.0.1:123456',
            $checkObj->getServiceName(),
            'ServicePortCheck::setServiceName test failed!'
        );

        $checkObj->setServiceName('Gearman');
        $this->assertEquals(
            'Gearman',
            $checkObj->getServiceName(),
            'ServicePortCheck::setServiceName test failed!'
        );
    }

    public function testSetServer()
    {
        $checkObj = new ServicePortCheck(new DI());
        $checkObj->setServer('127.0.0.1');
        $this->assertEquals(
            '127.0.0.1',
            $checkObj->getServer(),
            'ServicePortCheck::setServer test failed!'
        );
    }

    public function testSetPort()
    {
        $checkObj = new ServicePortCheck(new DI());
        $checkObj->setPort('11211');
        $this->assertEquals(
            '11211',
            $checkObj->getPort(),
            'ServicePortCheck::setPort test failed!'
        );
    }

    public function testGetMetrics()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('GearmanCheckFail');
        $expectedMetric->setUnit('Count');
        $expectedMetric->setValue('0');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $expectedFailMetric = new Metric();
        $expectedFailMetric->setName('ChatNodeJsCheckFail');
        $expectedFailMetric->setUnit('Count');
        $expectedFailMetric->setValue('1');
        $expectedFailMetric->setNamespace('CustomMetric/Test');

        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());

        $servicePortCheck = new ServicePortCheck($diObj, 'CustomMetric/Test');

        $servicePortCheck->setServer('127.0.0.1');
        $servicePortCheck->setPort('11211');
        $servicePortCheck->setServiceName('Gearman');

        $metrics = $servicePortCheck->getMetrics();
        $this->assertCount(1, $metrics, 'ServicePortCheck::getMetrics test failed!');
        $this->assertEquals($expectedMetric, $metrics[0], 'ServicePortCheck::getMetrics expected metric test failed!');

        $servicePortCheck->setServer('127.0.0.1');
        $servicePortCheck->setPort('3434');
        $servicePortCheck->setServiceName('ChatNodeJs');
        $metrics = $servicePortCheck->getMetrics();
        $this->assertCount(1, $metrics, 'ServicePortCheck::getMetrics test failed!');
        $this->assertEquals($expectedFailMetric, $metrics[0], 'ServicePortCheck::getMetrics check status test failed!');
    }
}
