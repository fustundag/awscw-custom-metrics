<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;
use Cron\CronExpression;

class MemoryUsageTest extends \Codeception\TestCase\Test
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
        $memUsage = new MemoryUsage(new CommandRunner());
        $this->assertNull($memUsage->getNamespace(), 'MemoryUsage::getNamespace null test failed!');

        $memUsage = new MemoryUsage(new CommandRunner(), '');
        $this->assertEquals('', $memUsage->getNamespace(), 'MemoryUsage::getNamespace empty string failed!');

        $memUsage = new MemoryUsage(new CommandRunner(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $memUsage->getNamespace(), 'MemoryUsage::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $memUsage = new MemoryUsage(new CommandRunner());
        $memUsage->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $memUsage->getNamespace(), 'MemoryUsage::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $memUsage = new MemoryUsage(new CommandRunner());
        $this->assertNull($memUsage->getCronExpression(), 'MemoryUsage::getCronExpression null test failed!');

        $cronExpression = CronExpression::factory('*/5 * * * *');
        $memUsage = new MemoryUsage(new CommandRunner(), '', $cronExpression);
        $this->assertEquals($cronExpression, $memUsage->getCronExpression(), 'MemoryUsage::getCronExpression failed!');
    }

    public function testSetCronExpression()
    {
        $cronExpression = CronExpression::factory('*/5 * * * *');
        $memUsage = new MemoryUsage(new CommandRunner());
        $memUsage->setCronExpression($cronExpression);
        $this->assertEquals($cronExpression, $memUsage->getCronExpression(), 'MemoryUsage::setCronExpression failed!');
    }

    public function testGetMetrics()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('MemoryUsage');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('60');
        $expectedMetric->setNamespace('CustomMetric/Test');
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnCode' => 0,
            'getOutput' => [
                'MemTotal:        10000 kB',
                'MemFree:          2000 kB',
                'MemAvailable:     419980 kB',
                'Buffers:          1000 kB',
                'Cached:           1000 kB',
                'SwapCached:            0 kB',
                'Active:           526652 kB',
                'Inactive:         164928 kB',
            ]
        ]);

        $memoryUsage = new MemoryUsage($fakeCmdRunner, 'CustomMetric/Test');
        $returnArray = $memoryUsage->getMetrics();
        $this->assertCount(1, $returnArray, 'Memory usage return array failed');
        $this->assertEquals($expectedMetric, $returnArray[0], 'MemoryUsage return metric object failed!');

        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnCode' => 0,
            'getOutput' => [
                'MemTotal:        10000 kB',
                'MemFree:          8000 kB',
                'MemAvailable:     419980 kB',
                'Buffers:          1000 kB',
                'Cached:           1000 kB',
                'SwapCached:            0 kB',
                'Active:           526652 kB',
                'Inactive:         164928 kB',
            ]
        ]);
        $memoryUsage   = new MemoryUsage($fakeCmdRunner, 'CustomMetric/Test');
        $returnArray = $memoryUsage->getMetrics();
        $this->assertNull($returnArray, 'MemoryUsage return null failed!');

        $this->expectOutputString(
            "[".date('Y-m-d H:i:s')."][ERROR] /proc/meminfo parse failed!, RETVAL: 255, OUT: Error occured\n"
        );
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnCode' => 255,
            'getOutput' => [
                'Error occured',
            ]
        ]);
        $memoryUsage   = new MemoryUsage($fakeCmdRunner, 'CustomMetric/Test', null, new DefaultLogger());
        $returnArray = $memoryUsage->getMetrics();
        $this->assertFalse($returnArray, 'MemoryUsage return false failed!');
    }

    public function testCreateNewMetric()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('MemoryUsage');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('56');
        $expectedMetric->setNamespace('CustomMetric/Test');
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnValue' => '56'
        ]);

        $memUsage = new MemoryUsage($fakeCmdRunner, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $memUsage->createNewMetric('MemoryUsage', 'Percent', '56'),
            'MemoryUsage::createNewMetric test failed!'
        );

    }
}
