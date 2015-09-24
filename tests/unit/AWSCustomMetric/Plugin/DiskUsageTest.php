<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;
use Cron\CronExpression;

class DiskUsageTest extends \Codeception\TestCase\Test
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
        $diskUsage = new DiskUsage(new DI());
        $this->assertNull($diskUsage->getNamespace(), 'DiskUsage::getNamespace null test failed!');

        $diskUsage = new DiskUsage(new DI(), '');
        $this->assertEquals('', $diskUsage->getNamespace(), 'DiskUsage::getNamespace empty string failed!');

        $diskUsage = new DiskUsage(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $diskUsage->getNamespace(), 'DiskUsage::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $diskUsage = new DiskUsage(new DI());
        $diskUsage->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $diskUsage->getNamespace(), 'DiskUsage::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $diskUsage = new DiskUsage(new DI());
        $this->assertNull($diskUsage->getCronExpression(), 'DiskUsage::getCronExpression null test failed!');

        $diskUsage = new DiskUsage(new DI(), null, '*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $diskUsage->getCronExpression(), 'DiskUsage::getCronExpression failed!');
    }

    public function testSetCronExpression()
    {
        $diskUsage = new DiskUsage(new DI());
        $diskUsage->setCronExpression('*/5 * * * *');
        $this->assertEquals('*/5 * * * *', $diskUsage->getCronExpression(), 'DiskUsage::setCronExpression failed!');
    }

    public function testGetMetrics()
    {
        $diObj = new DI();
        $expectedMetric = new Metric();
        $expectedMetric->setName('DiskUsage');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('56');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function () {

            },
            'getReturnCode'  => 0,
            'getReturnValue' => Stub::consecutive('Darwin', '56')
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);
        $diskUsage   = new DiskUsage($diObj, 'CustomMetric/Test');
        $returnArray = $diskUsage->getMetrics();
        $this->assertCount(1, $returnArray, 'Disk usage return array failed');
        $this->assertEquals($expectedMetric, $returnArray[0], 'DiskUsage return metric object failed!');

        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function () {

            },
            'getReturnCode'  => 0,
            'getReturnValue' => Stub::consecutive('Linux', '0')
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);
        $diskUsage   = new DiskUsage($diObj, 'CustomMetric/Test');
        $returnArray = $diskUsage->getMetrics();
        $this->assertNull($returnArray, 'DiskUsage return null failed!');
    }

    public function testCreateNewMetric()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('DiskUsage');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('56');
        $expectedMetric->setNamespace('CustomMetric/Test');
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => function () {

            },
            'getReturnCode'  => 0,
            'getReturnValue' => '56'
        ]);
        $diObj->setCommandRunner($fakeCmdRunner);

        $diskUsage = new DiskUsage($diObj, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $diskUsage->createNewMetric('DiskUsage', 'Percent', '56'),
            'DiskUsage::createNewMetric test failed!'
        );

    }

    public function testSetMountPoint()
    {
        $diskUsage = new DiskUsage(new DI());
        $diskUsage->setMountPoint('/home');
        $this->assertEquals('/home', $diskUsage->getMountPoint(), 'DiskUsage::setMountPoint test failed!');
    }

    public function testGetMountPoint()
    {
        $diskUsage = new DiskUsage(new DI());
        $this->assertEquals('/', $diskUsage->getMountPoint(), 'DiskUsage::getMountPoint default value test failed!');

        $diskUsage->setMountPoint('/home');
        $this->assertEquals('/home', $diskUsage->getMountPoint(), 'DiskUsage::getMountPoint test failed!');
    }
}
