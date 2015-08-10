<?php

namespace AWSCustomMetric\Logger;

class DefaultLogger implements LoggerInterface
{
    public function debug($msg)
    {
        echo "[".date('Y-m-d H:i:s')."][DEBUG] " . $msg . "\n";
    }

    public function info($msg)
    {
        echo "[".date('Y-m-d H:i:s')."][INFO] " . $msg . "\n";
    }

    public function error($msg)
    {
        echo "[".date('Y-m-d H:i:s')."][ERROR] " . $msg . "\n";
    }
}
