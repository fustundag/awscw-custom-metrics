<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Logger\LoggerInterface;
use AWSCustomMetric\Metric;

class MemoryUsage implements MetricPluginInterface
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
        $retVal = null;
        $memInfoLines = null;
        @exec('/bin/cat /proc/meminfo', $memInfoLines, $retVal);
        $memInfo = [];
        if ($retVal==0) {
            foreach ($memInfoLines as $memInfoLine) {
                list($key, $val) = explode(':', $memInfoLine);
                $key = trim($key);
                $val = intval(trim($val));
                $memInfo[ $key ] = $val;
            }
            $memInfo['MemAvail'] = $memInfo['MemFree'] + $memInfo['Buffers'] + $memInfo['Cached'];
            $memInfo['MemUsed']  = $memInfo['MemTotal'] - $memInfo['MemAvail'];
            $memInfo['MemUtil']  = ceil((100*$memInfo['MemUsed']/$memInfo['MemTotal']));

            if ($memInfo['MemUtil']>0) {
                $metric = new Metric();
                $metric->setNamespace($this->namespace);
                $metric->setName('MemoryUsage');
                $metric->setUnit('Percent');
                $metric->setValue($memInfo['MemUtil']);
                return [$metric];
            } else {
                return null;
            }
        } else {
            if ($this->logger) {
                $this->logger->error(
                    '/proc/meminfo parse failed!, RETVAL: ' . $retVal . ', OUT: ' . implode('|', $memInfoLines)
                );
            }
            return false;
        }
    }
}
