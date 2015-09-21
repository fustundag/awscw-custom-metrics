<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use AWSCustomMetric\Metric;

class UnitHelper extends \Codeception\Module
{
    public function getMetricDataArray($metrics, $instanceId, $ns)
    {
        $metricData = [];
        /* @var Metric $metric */
        foreach ($metrics as $metric) {
            $metricData[] = [
                'Dimensions' => [
                    ['Name' => 'InstanceId', 'Value' => $instanceId]
                ],
                'MetricName' => $metric->getName(),
                'Unit' => $metric->getUnit(),
                'Value' => $metric->getValue(),
                'Timestamp' => date('Y-m-d') . 'T' . date('H:i:s') . 'Z'
            ];
        }
        return [
            'Namespace'  => $ns,
            'MetricData' => $metricData
        ];
    }
}
