<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

class RedisCheck extends BaseMetricPlugin implements MetricPluginInterface
{
    private $server;
    private $port;
    private $keys = array();

    public function __construct(DI $diObj, $namespace = null, $cronExpression = '')
    {
        parent::__construct($diObj, $namespace, $cronExpression);
        $this->server = 'localhost';
        $this->port   = '6379';
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

    private function getServerPortArgs()
    {
        return '-h ' . $this->getServer() . ' -p ' . $this->getPort();
    }

    private function getRedisCliCmd()
    {
        return '/usr/bin/redis-cli ' . $this->getServerPortArgs();
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @return array|bool
     */
    private function findKeys()
    {
        try {
            $this->diObj->getCommandRunner()->execute($this->getRedisCliCmd() . ' --raw dbsize');
            $retVal = $this->diObj->getCommandRunner()->getReturnCode();
            $redisKeyCount = $this->diObj->getCommandRunner()->getOutput();
            error_log('redisKeyCount: ' . json_encode($redisKeyCount).PHP_EOL, 3, '/tmp/php_error.log');

            if ($retVal!==0) {
                if ($this->diObj->getLogger()) {
                    $this->diObj->getLogger()->error(
                        'Redis KEyCount cmd failed!, RETVAL: ' . $retVal
                        . ', OUT: ' . implode('|', $redisKeyCount)
                    );
                }
                return false;
            }
            if ($redisKeyCount[0]>0) {
                $this->diObj->getCommandRunner()->execute($this->getRedisCliCmd() . ' --raw keys *');
                $retVal = $this->diObj->getCommandRunner()->getReturnCode();
                $redisKeyList = $this->diObj->getCommandRunner()->getOutput();
                if ($retVal!==0) {
                    if ($this->diObj->getLogger()) {
                        $this->diObj->getLogger()->error(
                            'Redis KeyList cmd failed!, RETVAL: ' . $retVal
                            . ', OUT: ' . implode('|', $redisKeyList)
                        );
                    }
                    return false;
                }
                return $redisKeyList;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Redis client thrown exception! ExcpMsg: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * @return Metric[]|null|bool
     */
    public function getMetrics()
    {
        try {
            $redisKeyList = count($this->keys)==0?$this->findKeys():$this->keys;
            $totalLen     = 0;
            if ($redisKeyList && is_array($redisKeyList)) {
                foreach ($redisKeyList as $redisKey) {
                    $this->diObj->getCommandRunner()->execute(
                        $this->getRedisCliCmd() . ' --raw llen ' . trim($redisKey)
                    );
                    $retVal = $this->diObj->getCommandRunner()->getReturnCode();
                    $redisKeyLen = $this->diObj->getCommandRunner()->getOutput();
                    if ($retVal===0) {
                        $totalLen += intval($redisKeyLen[0]);
                    }
                }
            }
            return [
                $this->createNewMetric('RedisKeysLen', 'Count', $totalLen)
            ];
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('Redis client thrown exception! ExcpMsg: ' . $e->getMessage());
            }
            return false;
        }
    }
}
