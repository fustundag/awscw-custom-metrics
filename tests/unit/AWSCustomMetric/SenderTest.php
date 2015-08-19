<?php

namespace AWSCustomMetric;

use Aws\CloudWatch\CloudWatchClient;
use AWSCustomMetric\Logger\DefaultLogger;
use Codeception\Util\Stub;

class SenderTest extends \Codeception\TestCase\Test
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

    public function testAutoInstanceId()
    {
        /* @var CommandRunner $fakeCmdRunner */
        $fakeCmdRunner = Stub::make('\AWSCustomMetric\CommandRunner', [
            'execute' => true,
            'getReturnValue' => 'AutoInstance1'
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', $fakeCmdRunner);
        $this->assertEquals('AutoInstance1', $testObj->getInstanceId(), 'auto-instance find failed!');
    }
    public function testInitCloudWatchClient()
    {
        $expectedCWClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => 'fakeregion',
            'credentials' => [
                'key'   => 'fakekey',
                'secret' => 'fakesecret'
            ]
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->initCloudWatchClient();
        $this->assertEquals($expectedCWClient, $testObj->getCloudWatchClient(), 'initCloudWatchClient failed!');
    }

    public function testGetCloudWatchClient()
    {
        $expectedCWClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => 'fakeregion',
            'credentials' => [
                'key'   => 'fakekey',
                'secret' => 'fakesecret'
            ]
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $this->assertEquals($expectedCWClient, $testObj->getCloudWatchClient(), 'getCloudWatchClient failed!');
    }

    public function testSetAwsKey()
    {
        $expectedCWClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => 'fakeregion',
            'credentials' => [
                'key'   => 'fakekey2',
                'secret' => 'fakesecret'
            ]
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->setAwsKey('fakekey2');
        $this->assertEquals('fakekey2', $testObj->getAwsKey(), 'setAwsKey failed!');
        $this->assertEquals($expectedCWClient, $testObj->getCloudWatchClient(), 'setAwsKey - CloudWatchClient failed!');
    }

    public function testGetAwsKey()
    {
        $testObj = new Sender('fake_key', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $this->assertEquals('fake_key', $testObj->getAwsKey(), 'getAwsKey failed!');
    }

    public function testSetAwsSecret()
    {
        $expectedCWClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => 'fakeregion',
            'credentials' => [
                'key'   => 'fakekey',
                'secret' => 'fakesecret2'
            ]
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->setAwsSecret('fakesecret2');
        $this->assertEquals('fakesecret2', $testObj->getAwsSecret(), 'setAwsSecret failed!');
        $this->assertEquals($expectedCWClient, $testObj->getCloudWatchClient(), 'setAwsSecret - CloudWatchClient failed!');
    }

    public function testGetAwsSecret()
    {
        $testObj = new Sender('fakekey', 'fake_secret', 'fakeregion', new CommandRunner(), 'testInstance');
        $this->assertEquals('fake_secret', $testObj->getAwsSecret(), 'getAwsSecret failed!');
    }

    public function testSetRegion()
    {
        $expectedCWClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => 'fakeregion2',
            'credentials' => [
                'key'   => 'fakekey',
                'secret' => 'fakesecret'
            ]
        ]);

        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->setRegion('fakeregion2');
        $this->assertEquals('fakeregion2', $testObj->getRegion(), 'setRegion failed!');
        $this->assertEquals($expectedCWClient, $testObj->getCloudWatchClient(), 'setRegion - CloudWatchClient failed!');
    }

    public function testGetRegion()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fake_region', new CommandRunner(), 'testInstance');
        $this->assertEquals('fake_region', $testObj->getRegion(), 'getRegion failed!');
    }

    public function testSetNamespace()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->setNamespace('testns');
        $this->assertEquals('testns', $testObj->getNamespace(), 'setNamespace failed!');
    }

    public function testGetNamespace()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fake_region', new CommandRunner(), 'testInstance', 'testns');
        $this->assertEquals('testns', $testObj->getNamespace(), 'getNamespace failed!');
    }

    public function testSetLogger()
    {
        $logger  = new DefaultLogger();
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance');
        $testObj->setLogger($logger);
        $this->assertEquals($logger, $testObj->getLogger(), 'setLogger failed!');
    }

    public function testGetLogger()
    {
        $logger  = new DefaultLogger();
        $testObj = new Sender('fakekey', 'fakesecret', 'fake_region', new CommandRunner(), 'testInstance', 'testns');
        $testObj->setLogger($logger);
        $this->assertEquals($logger, $testObj->getLogger(), 'getLogger failed!');
    }

    public function testGetPlugins()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $testObj->addPlugin(['plugin1', 'plugin2', 'plugin3']);
        $this->assertEquals(['plugin1', 'plugin2', 'plugin3'], $testObj->getPlugins(), 'getPlugins failed!');
    }

    public function testAddPlugin()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $testObj->addPlugin('plugin1');
        $this->assertContains('plugin1', $testObj->getPlugins(), 'addPlugin failed!');
        $this->assertCount(1, $testObj->getPlugins(), 'addPlugin failed!');

        $testObj->addPlugin(['plugin2', 'plugin3', 'plugin1']);
        $this->assertContains('plugin2', $testObj->getPlugins(), 'addPlugin failed!');
        $this->assertContains('plugin3', $testObj->getPlugins(), 'addPlugin failed!');
        $this->assertCount(3, $testObj->getPlugins(), 'addPlugin failed!');
    }

    public function testRemovePlugin()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $testObj->addPlugin(['plugin1', 'plugin2', 'plugin3']);

        $testObj->removePlugin('plugin2');
        $this->assertContains('plugin1', $testObj->getPlugins(), 'removePlugin failed!');
        $this->assertContains('plugin3', $testObj->getPlugins(), 'removePlugin failed!');
        $this->assertNotContains('plugin2', $testObj->getPlugins(), 'removePlugin failed!');
        $this->assertCount(2, $testObj->getPlugins(), 'removePlugin failed!');
    }

    public function testGetInstanceId()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $this->assertEquals('testInstance', $testObj->getInstanceId(), 'getInstanceId failed!');
    }

    public function testSetInstanceId()
    {
        $testObj = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $testObj->setInstanceId('newinstanceid');
        $this->assertEquals('newinstanceid', $testObj->getInstanceId(), 'setInstanceId failed!');
    }

    public function testGetCmdRunner()
    {
        $cmdRunner = new CommandRunner();
        $testObj   = new Sender('fakekey', 'fakesecret', 'fakeregion', $cmdRunner, 'testInstance', 'testns');
        $this->assertEquals($cmdRunner, $testObj->getCmdRunner(), 'getCmdRunner failed!');
    }

    public function testSetCmdRunner()
    {
        $cmdRunner = new CommandRunner();
        $testObj   = new Sender('fakekey', 'fakesecret', 'fakeregion', new CommandRunner(), 'testInstance', 'testns');
        $testObj->setCmdRunner($cmdRunner);
        $this->assertEquals($cmdRunner, $testObj->getCmdRunner(), 'setCmdRunner failed!');
    }
}
