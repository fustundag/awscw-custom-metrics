<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

class MemcachedCheck extends BaseMetricPlugin implements MetricPluginInterface
{
    /**
     * @var \Memcached
     */
    private $memcached;
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
        $this->setMemcached();
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
        $this->setMemcached();
    }

    /**
     * @return \Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * @param \Memcached|null $memcached
     */
    public function setMemcached($memcached = null)
    {
        if ($memcached) {
            $this->memcached = $memcached;
        }
        if ($this->memcached) {
            $this->memcached->resetServerList();
            $this->memcached->addServer($this->server, $this->port);
        }
    }

    /**
     * @return Metric[]|null|bool
     */
    public function getMetrics()
    {
        try {
            $status = $this->memcached->set('awscustommetrics.test', 1, 2);

            if ($status===false) {
                return [
                    $this->createNewMetric('MemcachedCheckFail', 'Count', 1)
                ];
            } else {
                $testValue = $this->memcached->get('awscustommetrics.test');
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
