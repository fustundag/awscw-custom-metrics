<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;

class DiskUsage extends BaseMetricPlugin implements MetricPluginInterface
{
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
