<?php

namespace AWSCustomMetric;

use Aws\CloudWatch\CloudWatchClient;
use AWSCustomMetric\Logger\LoggerInterface;
use AWSCustomMetric\Plugin\MetricPluginInterface;

class Sender
{
    private $cloudWatchClient = null;
    private $awsKey     = null;
    private $awsSecret  = null;
    private $region     = null;
    private $instanceId = null;
    private $plugins    = [];
    private $namespace  = null;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    public function __construct($awsKey, $awsSecret, $region, $instanceId = null, $namespace = null)
    {
        $this->awsKey    = $awsKey;
        $this->awsSecret = $awsSecret;
        $this->region    = $region;

        $this->cloudWatchClient = new CloudWatchClient([
            'region' => $this->region,
            'credentials' => [
                'key'   => $this->awsKey,
                'secre' => $this->awsSecret
            ]
        ]);
        if ($instanceId) {
            $this->instanceId = $instanceId;
        } else {
            $this->instanceId  = @exec('/usr/bin/wget -q -O - http://169.254.169.254/latest/meta-data/instance-id');
        }

        $this->namespace = $namespace;
    }

    private function setCloudWatchClient()
    {
        $this->cloudWatchClient = new CloudWatchClient([
            'region' => $this->region,
            'credentials' => [
                'key'   => $this->awsKey,
                'secre' => $this->awsSecret
            ]
        ]);
    }

    /**
     * @param null $awsKey
     */
    public function setAwsKey($awsKey)
    {
        if ($awsKey && $awsKey!=$this->awsKey) {
            $this->awsKey = $awsKey;
            $this->setCloudWatchClient();
        }
    }

    /**
     * @param null $awsSecret
     */
    public function setAwsSecret($awsSecret)
    {
        if ($awsSecret && $awsSecret!=$this->awsSecret) {
            $this->$awsSecret = $awsSecret;
            $this->setCloudWatchClient();
        }
    }

    /**
     * @param null $region
     */
    public function setRegion($region)
    {
        if ($region && $region!=$this->region) {
            $this->region = $region;
            $this->setCloudWatchClient();
        }
    }

    /**
     * @param null $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string|array $plugin
     */
    public function addPlugin($plugin)
    {
        if (is_string($plugin)) {
            $plugin = [$plugin];
        }
        $this->plugins = array_unique(array_merge($this->plugins, $plugin));
    }

    /**
     * @param string $plugin
     */
    public function removePlugin($plugin)
    {
        if (in_array($plugin, $this->plugins)) {
            $this->plugins = array_diff($this->plugins, [$plugin]);
        }
    }

    private function sendMetric(Metric $metric)
    {
        $this->cloudWatchClient->putMetricData([
            'Namespace'  => $metric->getNamespace(),
            'MetricData' => [
                'Dimensions' => [
                    ['Name' => 'InstanceId', 'Value' => $this->instanceId]
                ],
                'MetricName' => $metric->getName(),
                'Unit' => $metric->getUnit(),
                'Value' => $metric->getValue(),
                'Timestamp' => date('Y-m-d') . 'T' . date('H:i:s') . 'Z'
            ]
        ]);
    }

    public function run()
    {
        foreach ($this->plugins as $plugin) {
            $pluginClassName = "AWSCustomMetric\\Plugin\\" . $plugin;
            if (class_exists($pluginClassName)) {
                /* @var MetricPluginInterface $pluginObj */
                $pluginObj = new $pluginClassName($this->namespace, $this->logger, new CommandRunner());
                $metrics   = $pluginObj->getMetrics();
                if (is_array($metrics)) {
                    foreach ($metrics as $metric) {
                        $this->sendMetric($metric);
                    }
                }
            }
        }
    }
}
