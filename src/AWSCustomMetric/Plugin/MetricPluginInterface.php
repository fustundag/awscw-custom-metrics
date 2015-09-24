<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;

interface MetricPluginInterface
{
    public function __construct(DI $diObj, $namespace = null, $cronExpression = '');
    public function setCronExpression($cronExpression);

    /**
     * @return string
     */
    public function getCronExpression();

    public function setNamespace($namespace);

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @return array|null|bool
     */
    public function getMetrics();
}
