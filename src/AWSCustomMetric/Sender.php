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
     * @var CommandRunner
     */
    private $cmdRunner;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    public function __construct(
        $awsKey,
        $awsSecret,
        $region,
        CommandRunner $cmdRunner,
        $instanceId = null,
        $namespace = null
    )
    {
        $this->cmdRunner = $cmdRunner;
        $this->awsKey    = $awsKey;
        $this->awsSecret = $awsSecret;
        $this->region    = $region;
        if ($instanceId) {
            $this->instanceId = $instanceId;
        } else {
            $this->cmdRunner->execute('/usr/bin/wget -q -O - http://169.254.169.254/latest/meta-data/instance-id');
            $this->instanceId = $this->cmdRunner->getReturnValue();
        }
        $this->namespace = $namespace;
        $this->initCloudWatchClient();
    }

    public function initCloudWatchClient()
    {
        $this->cloudWatchClient = new CloudWatchClient([
            'version' => '2010-08-01',
            'region' => $this->region,
            'credentials' => [
                'key'   => $this->awsKey,
                'secret' => $this->awsSecret
            ]
        ]);
    }

    /**
     * @return CloudWatchClient|null
     */
    public function getCloudWatchClient()
    {
        return $this->cloudWatchClient;
    }

    /**
     * @param string $awsKey
     */
    public function setAwsKey($awsKey)
    {
        if ($awsKey && $awsKey!=$this->awsKey) {
            $this->awsKey = $awsKey;
            $this->initCloudWatchClient();
        }
    }

    /**
     * @return string
     */
    public function getAwsKey()
    {
        return $this->awsKey;
    }

    /**
     * @param string $awsSecret
     */
    public function setAwsSecret($awsSecret)
    {
        if ($awsSecret && $awsSecret!=$this->awsSecret) {
            $this->awsSecret = $awsSecret;
            $this->initCloudWatchClient();
        }
    }

    /**
     * @return string
     */
    public function getAwsSecret()
    {
        return $this->awsSecret;
    }

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        if ($region && $region!=$this->region) {
            $this->region = $region;
            $this->initCloudWatchClient();
        }
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
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
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string|array $plugin
     */
    public function addPlugin($plugin)
    {
        if ($plugin) {
            if (is_string($plugin)) {
                $plugin = [$plugin];
            }
            $this->plugins = array_unique(array_merge($this->plugins, $plugin));
        }
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

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param string $instanceId
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * @return CommandRunner
     */
    public function getCmdRunner()
    {
        return $this->cmdRunner;
    }

    /**
     * @param CommandRunner $cmdRunner
     */
    public function setCmdRunner(CommandRunner $cmdRunner)
    {
        $this->cmdRunner = $cmdRunner;
    }

    private function sendMetric(Metric $metric)
    {
        $this->cloudWatchClient->putMetricData([
            'Namespace'  => $metric->getNamespace(),
            'MetricData' => [[
                'Dimensions' => [
                    ['Name' => 'InstanceId', 'Value' => $this->instanceId]
                ],
                'MetricName' => $metric->getName(),
                'Unit' => $metric->getUnit(),
                'Value' => $metric->getValue(),
                'Timestamp' => date('Y-m-d') . 'T' . date('H:i:s') . 'Z'
            ]]
        ]);
    }

    public function run()
    {
        foreach ($this->plugins as $plugin) {
            $pluginClassName = "AWSCustomMetric\\Plugin\\" . $plugin;
            if (class_exists($pluginClassName)) {
                /* @var MetricPluginInterface $pluginObj */
                $pluginObj = new $pluginClassName($this->namespace, $this->logger, $this->cmdRunner);
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
