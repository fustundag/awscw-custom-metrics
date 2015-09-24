<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;

class MemoryUsage extends BaseMetricPlugin implements MetricPluginInterface
{
    private $swapCheckOn = true;

    /**
     * @return boolean
     */
    public function isSwapCheckOn()
    {
        return $this->swapCheckOn;
    }

    /**
     * @param bool $swapCheckOn
     */
    public function setSwapCheckOn($swapCheckOn)
    {
        $this->swapCheckOn = $swapCheckOn?true:false;
    }

    /**
     * @return Metric[]|bool
     */
    public function getMetrics()
    {
        $this->diObj->getCommandRunner()->execute('/bin/cat /proc/meminfo');
        $retVal = $this->diObj->getCommandRunner()->getReturnCode();
        $memInfoLines = $this->diObj->getCommandRunner()->getOutput();
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
            $memInfo['MemUtil']  = $memInfo['MemTotal']>0?ceil((100*$memInfo['MemUsed']/$memInfo['MemTotal'])):100;

            $metrics = [ $this->createNewMetric('MemoryUsage', 'Percent', $memInfo['MemUtil']) ];

            if ($this->isSwapCheckOn()) {
                $memInfo['SwapUsed'] = $memInfo['SwapTotal'] - $memInfo['SwapFree'];
                $memInfo['SwapUtil'] = $memInfo['SwapTotal']>0
                    ?ceil((100*$memInfo['SwapUsed']/$memInfo['SwapTotal']))
                    :100;
                $metrics[] = $this->createNewMetric('SwapUsage', 'Percent', $memInfo['SwapUtil']);
            }

            return $metrics;
        } else {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error(
                    '/proc/meminfo parse failed!, RETVAL: ' . $retVal . ', OUT: ' . implode('|', $memInfoLines)
                );
            }
            return false;
        }
    }
}
