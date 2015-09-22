<?php
/**
 * Copyright (c) Fatih Ustundag <fatih.ustundag@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\Logger\LoggerInterface;
use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\Metric;
use Cron\CronExpression;

abstract class BaseMetricPlugin implements MetricPluginInterface
{
    /**
     * @var CommandRunner
     */
    protected $cmdRunner;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $namespace;

    /**
     * @var CronExpression
     */
    protected $cronExpression = null;

    public function __construct(
        CommandRunner $cmdRunner,
        $namespace = null,
        CronExpression $cronExpression = null,
        LoggerInterface $logger = null
    ) {
        $this->cmdRunner = $cmdRunner;
        if ($namespace) {
            $this->namespace = $namespace;
        }
        $this->cronExpression = $cronExpression;
        $this->logger         = $logger;
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
     * @return CronExpression
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * @param CronExpression $cronExpression
     */
    public function setCronExpression(CronExpression $cronExpression)
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
