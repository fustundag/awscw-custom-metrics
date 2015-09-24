<?php

namespace AWSCustomMetric;

use AWSCustomMetric\Logger\LoggerInterface;
use Cron\CronExpression;
use GuzzleHttp\Client;

/**
 * Class DI
 * @package AWSCustomMetric
 * @method bool setCommandRunner($commandRunner)
 * @method CommandRunner getCommandRunner
 * @method bool setLogger($logger)
 * @method LoggerInterface getLogger
 * @method bool setCronExpression($cronExpression)
 * @method CronExpression getCronExpression
 * @method bool setGuzzleHttpClient($client)
 * @method Client getGuzzleHttpClient
 */

class DI
{
    private $objList = [];

    public function __call($name, $arguments)
    {
        $action  = substr($name, 0, 3);
        $objName = substr($name, 3);
        if ($action=='set') {
            $this->objList[ $objName ] = $arguments[0];
            return true;
        } else {
            return isset($this->objList[ $objName ])?$this->objList[ $objName ]:null;
        }
    }
}
