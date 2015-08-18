<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\Logger\LoggerInterface;

interface MetricPluginInterface
{
    public function __construct($namespace = '', LoggerInterface $logger = null, CommandRunner $cmdRunner = null);
    public function getNamespace();
    public function getMetrics();
}
