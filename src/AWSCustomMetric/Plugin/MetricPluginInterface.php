<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\CommandRunner;
use AWSCustomMetric\Logger\LoggerInterface;
use Cron\CronExpression;

interface MetricPluginInterface
{
    public function __construct(
        CommandRunner $cmdRunner,
        $namespace = null,
        CronExpression $cronExpression = null,
        LoggerInterface $logger = null
    );
    public function setCronExpression(CronExpression $cronExpression);

    /**
     * @return CronExpression
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
