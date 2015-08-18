<?php

namespace AWSCustomMetric;

class CommandRunnerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $testDirname;

    protected function _before()
    {
        $this->testDirname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cmd_runner_test';
        @mkdir($this->testDirname, 0777);
        @touch($this->testDirname . DIRECTORY_SEPARATOR . 'test1.txt');
        @touch($this->testDirname . DIRECTORY_SEPARATOR . 'test2.txt');
        @chdir($this->testDirname);
    }

    protected function _after()
    {
        @exec('rm -rf ' . $this->testDirname);
    }

    public function testExecute()
    {
        $cmdRunner = new CommandRunner();
        $cmdRunner->execute('ls -1');
        $this->assertEquals(0, $cmdRunner->getReturnCode());
        $this->assertEquals('test2.txt', $cmdRunner->getReturnValue());
        $this->assertEquals(['test1.txt', 'test2.txt'], $cmdRunner->getOutput());
    }

    public function testGetReturnCode()
    {
        $cmdRunner = new CommandRunner();
        $cmdRunner->execute('ls');
        $this->assertEquals(0, $cmdRunner->getReturnCode());
    }

    public function testGetReturnValue()
    {
        $cmdRunner = new CommandRunner();
        $cmdRunner->execute('pwd');
        $this->assertEquals($this->testDirname, $cmdRunner->getReturnValue());
    }

    public function testGetOutput()
    {
        $cmdRunner = new CommandRunner();
        $cmdRunner->execute('pwd');
        $this->assertEquals([$this->testDirname], $cmdRunner->getOutput());
    }
}
