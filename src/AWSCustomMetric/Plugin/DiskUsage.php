<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Metric;

class DiskUsage extends BaseMetricPlugin implements MetricPluginInterface
{
    /**
     * @return Metric[]|null
     */
    public function getMetrics()
    {
        $this->cmdRunner->execute('/bin/df -k -l --output=pcent /');
        $diskUtil = intval($this->cmdRunner->getReturnValue());
        if ($diskUtil>0) {
            return [ $this->createNewMetric('DiskUsage', 'Percent', $diskUtil) ];
        } else {
            return null;
        }
    }
}
