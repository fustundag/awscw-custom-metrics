<?php

namespace Aws\CloudWatch;

class CloudWatchClient
{
    public function __construct($param)
    {
        $fp = fopen(
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cloud_watch_client.txt',
            'w'
        );
        fwrite($fp, '');
        fclose($fp);
    }

    public function putMetricData($param)
    {
        $fp = fopen(
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cloud_watch_client.txt',
            'a+'
        );
        fwrite($fp, json_encode($param) . "\n");
        fclose($fp);
    }
}
