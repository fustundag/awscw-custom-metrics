<?php
// Here you can initialize variables that will be available to your tests

function loadMyFakeClasses($class)
{
    if ('Aws\CloudWatch\CloudWatchClient' === $class) {
        require __DIR__ . '/FakeCloudWatchClient.php';
        return true;
    }
}

spl_autoload_register('loadMyFakeClasses', true, true);
