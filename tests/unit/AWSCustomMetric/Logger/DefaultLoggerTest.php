<?php

namespace AWSCustomMetric\Logger;

class DefaultLoggerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testDebug()
    {
        $logger = new DefaultLogger();
        $this->expectOutputString("[".date('Y-m-d H:i:s')."][DEBUG] test msg\n");
        $logger->debug('test msg');
    }

    public function testInfo()
    {
        $logger = new DefaultLogger();
        $this->expectOutputString("[".date('Y-m-d H:i:s')."][INFO] test msg\n");
        $logger->info('test msg');
    }

    public function testError()
    {
        $logger = new DefaultLogger();
        $this->expectOutputString("[".date('Y-m-d H:i:s')."][ERROR] test msg\n");
        $logger->error('test msg');
    }

}
