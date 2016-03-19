<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

class ServicePortCheck extends BaseMetricPlugin implements MetricPluginInterface
{
    private $serviceName;
    private $server;
    private $port;

    public function __construct(DI $diObj, $namespace = null, $cronExpression = '')
    {
        parent::__construct($diObj, $namespace, $cronExpression);
    }

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName?:$this->server . ':' . $this->port;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return Metric[]|null|bool
     */
    public function getMetrics()
    {
        try {
            $fp = fsockopen($this->server, $this->port, $errno, $errstr, 5);

            if (!$fp) {
                return [
                    $this->createNewMetric($this->getServiceName() . 'CheckFail', 'Count', 1)
                ];
            } else {
                fclose($fp);
                return [
                    $this->createNewMetric($this->getServiceName() . 'CheckFail', 'Count', 0)
                ];
            }
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error(
                    'Service port check thrown exception!'
                    .' Service:' . $this->getServiceName() . ', Msg: ' . $e->getMessage()
                );
            }
            return [$this->createNewMetric($this->getServiceName() . 'CheckFail', 'Count', 1)];
        }
    }
}
