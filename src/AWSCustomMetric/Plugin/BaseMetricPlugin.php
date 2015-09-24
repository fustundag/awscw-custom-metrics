<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

abstract class BaseMetricPlugin implements MetricPluginInterface
{
    /**
     * @var DI
     */
    protected $diObj;
    protected $namespace;
    protected $cronExpression;

    public function __construct(DI $diObj, $namespace = null, $cronExpression = '')
    {
        $this->diObj = $diObj;
        if ($namespace) {
            $this->namespace = $namespace;
        }
        if ($cronExpression) {
            $this->cronExpression = $cronExpression;
        }
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * @param string $cronExpression
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = $cronExpression;
    }

    /**
     * @param $name
     * @param $unit
     * @param $value
     * @return Metric
     */
    public function createNewMetric($name, $unit, $value)
    {
        $metric = new Metric();
        $metric->setNamespace($this->namespace);
        $metric->setName($name);
        $metric->setUnit($unit);
        $metric->setValue($value);
        return $metric;
    }
}
