<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;

class DiskUsage extends BaseMetricPlugin implements MetricPluginInterface
{
    private $mountPoint = '/';

    /**
     * @return string
     */
    public function getMountPoint()
    {
        return $this->mountPoint;
    }

    /**
     * @param string $mountPoint
     */
    public function setMountPoint($mountPoint)
    {
        $this->mountPoint = $mountPoint;
    }

    /**
     * @return Metric[]|null
     */
    public function getMetrics()
    {
        $this->diObj->getCommandRunner()->execute('uname -s');
        $osName = $this->diObj->getCommandRunner()->getReturnValue();
        switch ($osName) {
            case 'Darwin':
                $this->diObj->getCommandRunner()->execute('/bin/df -k -l ' . $this->mountPoint . " | awk '{print $8}'");
                break;
            case 'Linux':
                $this->diObj->getCommandRunner()->execute('/bin/df -k -l ' . $this->mountPoint . " | awk '{print $5}'");
                break;
            default:
                $this->diObj->getCommandRunner()->execute('/bin/df -k -l ' . $this->mountPoint . " | awk '{print $5}'");
                break;
        }

        $diskUtil = intval($this->diObj->getCommandRunner()->getReturnValue());
        if ($diskUtil>0) {
            return [ $this->createNewMetric('DiskUsage', 'Percent', $diskUtil) ];
        } else {
            return null;
        }
    }
}
