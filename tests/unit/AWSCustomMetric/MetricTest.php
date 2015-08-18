<?php
namespace AWSCustomMetric;

class MetricTest extends \Codeception\TestCase\Test
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

    // tests
    public function testGetName()
    {
        $metric = new Metric();
        $metric->setName('memory');
        $this->tester->assertEquals('memory', $metric->getName(), 'Metric::getName failed!');
    }

    public function testSetName()
    {
        $metric = new Metric();
        $metric->setName('memory');
        $this->tester->assertEquals('memory', $metric->getName(), 'Metric::setName failed!');
    }

    public function testGetNamespace()
    {
        $metric = new Metric();
        $metric->setNamespace('Metric/Test');
        $this->tester->assertEquals('Metric/Test', $metric->getNamespace(), 'Metric::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $metric = new Metric();
        $metric->setNamespace('Metric/Test');
        $this->tester->assertEquals('Metric/Test', $metric->getNamespace(), 'Metric::setNamespace failed!');
    }

    public function testGetValue()
    {
        $metric = new Metric();
        $metric->setValue(12.345);
        $this->tester->assertEquals(12.345, $metric->getValue(), 'Metric::getValue failed!');
    }

    public function testSetValue()
    {
        $metric = new Metric();
        $metric->setValue(12.345);
        $this->tester->assertEquals(12.345, $metric->getValue(), 'Metric::setValue failed!');
    }

    public function testGetUnit()
    {
        $metric = new Metric();
        $metric->setUnit('Percent');
        $this->tester->assertEquals('Percent', $metric->getUnit(), 'Metric::getUnit failed!');
    }

    public function testSetUnit()
    {
        $metric = new Metric();
        $this->tester->assertFalse($metric->setUnit('Invalid'), 'Metric::setUnit invalid unit set failed!');

        $metric->setUnit('Kilobits/Second');
        $this->tester->assertEquals('Kilobits/Second', $metric->getUnit(), 'Metric::setUnit failed!');
    }
}