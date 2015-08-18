<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;
use Codeception\Util\Stub;

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
        $diskUsage = new DiskUsage('');
        $this->assertEquals('CustomMetric/System', $diskUsage->getNamespace(), 'DiskUsage::getNamespace failed!');

        $diskUsage = new DiskUsage('MyNamespace');
        $this->assertEquals('MyNamespace', $diskUsage->getNamespace(), 'DiskUsage::getNamespace failed!');
    }

    public function testGetMetrics()
    {
        $expectedMetric = new Metric();
        $expectedMetric->setName('DiskUsage');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('56');
        $expectedMetric->setNamespace('CustomMetric/Test');
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnValue' => '56'
        ]);

        $diskUsage   = new DiskUsage('CustomMetric/Test', null, $fakeCmdRunner);
        $returnArray = $diskUsage->getMetrics();
        $this->assertCount(1, $returnArray, 'Disk usage return array failed');
        $this->assertEquals($expectedMetric, $returnArray[0], 'DiskUsage return metric object failed!');

        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'getReturnValue' => '0'
        ]);
        $diskUsage   = new DiskUsage('CustomMetric/Test', null, $fakeCmdRunner);
        $returnArray = $diskUsage->getMetrics();
        $this->assertNull($returnArray, 'DiskUsage return null failed!');
    }
}
