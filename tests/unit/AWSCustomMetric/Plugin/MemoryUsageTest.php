<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;

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
        $memoryUsage = new MemoryUsage('');
        $this->assertEquals('CustomMetric/System', $memoryUsage->getNamespace(), 'MemoryUsage::getNamespace failed!');

        $memoryUsage = new MemoryUsage('MyNamespace');
        $this->assertEquals('MyNamespace', $memoryUsage->getNamespace(), 'MemoryUsage::getNamespace failed!');
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

        $memoryUsage = new MemoryUsage('CustomMetric/Test', null, $fakeCmdRunner);
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
        $memoryUsage   = new MemoryUsage('CustomMetric/Test', null, $fakeCmdRunner);
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
        $memoryUsage   = new MemoryUsage('CustomMetric/Test', new DefaultLogger(), $fakeCmdRunner);
        $returnArray = $memoryUsage->getMetrics();
        $this->assertFalse($returnArray, 'MemoryUsage return false failed!');
    }
}
