<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

class MemcachedCheck extends BaseMetricPlugin implements MetricPluginInterface
{
    private $server;
    private $port;

    public function __construct(DI $diObj, $namespace = null, $cronExpression = '')
    {
        parent::__construct($diObj, $namespace, $cronExpression);
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
            $memcached = new \Memcached();
            $memcached->addServer($this->server, $this->port);
            $status = $memcached->set('awscustommetrics.test', 1, 2);

            if ($status===false) {
                return [
                    $this->createNewMetric('MemcachedCheckFail', 'Count', 1)
                ];
            } else {
                $testValue = $memcached->get('awscustommetrics.test');
                if (!$testValue || $testValue!==1) {
                    return [
                        $this->createNewMetric('MemcachedCheckFail', 'Count', 1)
                    ];
                }

                // All things seems OK
                return [
                    $this->createNewMetric('MemcachedCheckFail', 'Count', 0)
                ];
            }
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Memcached client thrown exception! ExcpMsg: ' . $e->getMessage());
            }
            return [
                $this->createNewMetric('MemcachedCheckFail', 'Count', 1)
            ];
        }
    }
}
