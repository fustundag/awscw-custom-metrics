<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\Logger\LoggerInterface;
use AWSCustomMetric\Metric;

class DiskUsage implements MetricPluginInterface
{
    /**
     * @var CommandRunner
     */
    private $cmdRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $namespace = 'CustomMetric/System';

    public function __construct($namespace = '', LoggerInterface $logger = null, CommandRunner $cmdRunner = null)
    {
        if ($namespace) {
            $this->namespace = $namespace;
        }
        $this->logger = $logger;
        $this->cmdRunner = $cmdRunner;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return array|null
     */
    public function getMetrics()
    {
        $this->cmdRunner->execute('/bin/df -k -l --output=pcent /');
        $diskUtil = intval($this->cmdRunner->getReturnValue());
        if ($diskUtil>0) {
            $metric = new Metric();
            $metric->setNamespace($this->namespace);
            $metric->setName('DiskUsage');
            $metric->setUnit('Percent');
            $metric->setValue($diskUtil);
            return [$metric];
        } else {
            return null;
        }
    }
}
