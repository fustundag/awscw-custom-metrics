<?php

namespace AWSCustomMetric;

class CommandRunner
{
    private $output = null;
    private $returnCode = null;
    private $returnValue = null;

    public function __construct()
    {

    }

    public function execute($cmd)
    {
        $this->output      = null;
        $this->returnCode  = null;
        $this->returnValue = null;
        $this->returnValue = exec($cmd, $this->output, $this->returnCode);
    }

    /**
     * @return array|null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return int|null
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @return mixed|null
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }


}
