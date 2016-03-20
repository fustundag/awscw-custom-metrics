<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Metric;
use Codeception\Util\Stub;

class FileWatchTest extends \Codeception\TestCase\Test
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
        $objToCheck = new FileWatch(new DI());
        $this->assertNull($objToCheck->getNamespace(), 'FileWatch::getNamespace null test failed!');

        $objToCheck = new FileWatch(new DI(), '');
        $this->assertEquals('', $objToCheck->getNamespace(), 'FileWatch::getNamespace empty string failed!');

        $objToCheck = new FileWatch(new DI(), 'MyNamespace');
        $this->assertEquals('MyNamespace', $objToCheck->getNamespace(), 'FileWatch::getNamespace failed!');
    }

    public function testSetNamespace()
    {
        $objToCheck = new FileWatch(new DI());
        $objToCheck->setNamespace('TestSpace');
        $this->assertEquals('TestSpace', $objToCheck->getNamespace(), 'FileWatch::setNamespace failed!');
    }

    public function testGetCronExpression()
    {
        $objToCheck = new FileWatch(new DI());
        $this->assertNull($objToCheck->getCronExpression(), 'FileWatch::getCronExpression null test failed!');

        $objToCheck = new FileWatch(new DI(), '', '*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $objToCheck->getCronExpression(),
            'FileWatch::getCronExpression failed!'
        );
    }

    public function testSetCronExpression()
    {
        $objToCheck = new FileWatch(new DI(), '');
        $objToCheck->setCronExpression('*/5 * * * *');
        $this->assertEquals(
            '*/5 * * * *',
            $objToCheck->getCronExpression(),
            'FileWatch::setCronExpression failed!'
        );
    }

    public function testCreateNewMetric()
    {
        $diObj = new DI();

        $expectedMetric = new Metric();
        $expectedMetric->setName('FileWatch');
        $expectedMetric->setUnit('Percent');
        $expectedMetric->setValue('2');
        $expectedMetric->setNamespace('CustomMetric/Test');

        $objToCheck = new FileWatch($diObj, 'CustomMetric/Test');
        $this->assertEquals(
            $expectedMetric,
            $objToCheck->createNewMetric('FileWatch', 'Percent', '2'),
            'FileWatch::createNewMetric test failed!'
        );
    }

    public function testSetFileToWatch()
    {
        $objToCheck = new FileWatch(new DI());
        $objToCheck->setFileToWatch('abc.log');
        $this->assertEquals(
            'abc.log',
            $objToCheck->getFileToWatch(),
            'FileWatch::setFileToWatch test failed!'
        );

        $objToCheck->setFileToWatch('abc.Y-m-d.log', 'Y-m-d');
        $this->assertEquals(
            'abc.'. date('Y-m-d').'.log',
            $objToCheck->getFileToWatch(),
            'FileWatch::setFileToWatch test failed!'
        );
    }

    public function testCheckPointFile()
    {
        $objToCheck = new FileWatch(new DI());
        $objToCheck->setCheckPointFile('abc.log.checkpoint');
        $this->assertEquals(
            'abc.log.checkpoint',
            $objToCheck->getCheckPointFile(),
            'FileWatch::setCheckPointFile test failed!'
        );
    }

    public function testAddPattern()
    {
        $objToCheck = new FileWatch(new DI());
        $this->assertEquals([], $objToCheck->getPatterns(), 'FileWatch initial empty patterns test failed!');

        $objToCheck->addPattern('[EMERGENCY]');
        $this->assertArrayHasKey('[EMERGENCY]', $objToCheck->getPatterns(), 'FileWatch::addPattern test failed!');
        $this->assertEquals(false, $objToCheck->getPatterns()['[EMERGENCY]'], 'FileWatch::addPattern test failed!');

        $objToCheck->addPattern('[ALERT]', true);
        $this->assertArrayHasKey('[ALERT]', $objToCheck->getPatterns(), 'FileWatch::addPattern-regExp test failed!');
        $this->assertEquals(true, $objToCheck->getPatterns()['[ALERT]'], 'FileWatch::addPattern-regExp test failed!');
    }

    public function testRemovePattern()
    {
        $objToCheck = new FileWatch(new DI());
        $objToCheck->addPattern('[EMERGENCY]');
        $objToCheck->addPattern('[ALERT]');
        $objToCheck->removePattern('[ALERT]');

        $this->assertArrayNotHasKey('[ALERT]', $objToCheck->getPatterns(), 'FileWatch::removePattern test failed!');
    }

    public function testCheckLine()
    {
        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());
        $objToCheck = new FileWatch($diObj);
        $objToCheck->addPattern('[EMERGENCY]');
        $objToCheck->addPattern('[ALERT]');

        $this->assertEquals(
            1,
            $objToCheck->checkLine('[23ds] [ dsdsd ][ALERT] deneme'),
            'FileWatch::checkLine test failed!'
        );

        $this->assertEquals(
            0,
            $objToCheck->checkLine('[23ds] [ dsdsd ][INFO] deneme'),
            'FileWatch::checkLine test failed!'
        );

        $this->assertEquals(
            1,
            $objToCheck->checkLine('[EMERGENCY] [ dsdsd ][tarih] deneme'),
            'FileWatch::checkLine test failed!'
        );

        $objToCheck->addPattern('/(\d{4}\-\d{2}\-\d{2})+/iu', true);
        $this->assertEquals(
            1,
            $objToCheck->checkLine('[WARNING] [ dsdsd ][2016-03-13 23:23:59] deneme'),
            'FileWatch::checkLine regexp test failed!'
        );
    }

    public function testGetMetrics()
    {
        $diObj = new DI();
        $diObj->setLogger(new DefaultLogger());

        $objToCheck = new FileWatch($diObj, 'CustomMetric/Test');
        $objToCheck->addPattern('ALERT');
        $objToCheck->addPattern('EMERGENCY');
        $objToCheck->setFileToWatch('notfound.log');
        $metrics = $objToCheck->getMetrics();
        $this->assertCount(1, $metrics, 'FileWatch::getMetrics notfound test failed!');
        $this->assertEquals(
            new Metric('FileWatchException', 'Count', 1, 'CustomMetric/Test'),
            $metrics[0],
            'FileWatch::getMetrics notfound test failed!'
        );

        $testFile = '/tmp/' . uniqid() . '.log';
        touch($testFile);
        $objToCheck->setFileToWatch($testFile);

        $metrics = $objToCheck->getMetrics();
        $this->assertCount(1, $metrics, 'FileWatch::getMetrics zerosize test failed!');
        $this->assertEquals(
            new Metric('FileWatchError', 'Count', 0, 'CustomMetric/Test'),
            $metrics[0],
            'FileWatch::getMetrics zerosize test failed!'
        );

        $testStr = "[INFO] deneme\n[ALERT] deneme2\n[WARNING] deneme3\n";
        file_put_contents($testFile, $testStr);
        $metrics = $objToCheck->getMetrics();
        $this->assertCount(1, $metrics, 'FileWatch::getMetrics test failed!');
        $this->assertEquals(
            new Metric('FileWatchError', 'Count', 1, 'CustomMetric/Test'),
            $metrics[0],
            'FileWatch::getMetrics test failed!'
        );
        $this->assertFileExists($testFile . '.checkpoint', 'FileWatch::getMetrics checkpoint file failed!');
        $this->assertEquals(
            strlen($testStr),
            file_get_contents($testFile . '.checkpoint'),
            'FileWatch::getMetrics checkpoint file failed!'
        );
    }
}
