<?php

namespace AWSCustomMetric\Logger;

interface LoggerInterface
{
    public function debug($msg);
    public function info($msg);
    public function error($msg);
}
