<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Logger\LoggerInterface;
use AWSCustomMetric\Metric;

class DiskUsage implements MetricPluginInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $namespace = 'CustomMetric/System';

    public function __construct($namespace = '', LoggerInterface $logger = null)
    {
        if ($namespace) {
            $this->namespace = $namespace;
        }
        $this->logger = $logger;
    }

    /**
     * @return array|bool|null
     */
    public function getMetrics()
    {
        $retVal   = null;
        $out      = null;
        $diskUtil = intval(@exec('/bin/df -k -l --output=pcent /', $out, $retVal));
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
