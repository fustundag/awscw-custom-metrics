<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;

class MemoryUsage extends BaseMetricPlugin implements MetricPluginInterface
{
    /**
     * @return Metric[]|bool|null
     */
    public function getMetrics()
    {
        $this->cmdRunner->execute('/bin/cat /proc/meminfo');
        $retVal = $this->cmdRunner->getReturnCode();
        $memInfoLines = $this->cmdRunner->getOutput();
        $memInfo = [];
        if ($retVal===0 && is_array($memInfoLines) && count($memInfoLines)>0) {
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
                return [ $this->createNewMetric('MemoryUsage', 'Percent', $memInfo['MemUtil']) ];
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
