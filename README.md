# AWS CloudWatch Custom Metrics [![Build Status](https://travis-ci.org/fustundag/awscw-custom-metrics.svg?branch=master)](https://travis-ci.org/fustundag/awscw-custom-metrics) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fustundag/awscw-custom-metrics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fustundag/awscw-custom-metrics/?branch=master)

You can send custom metrics to AWS CloudWatch like disk/memory usage

## Features
* Send custom metrics to AWS CloudWatch
* Add new metrics what you need by metric plugins
* Configure different cron time for each metric plugin

## Requirements
* PHP 5.5+
* aws/aws-sdk-php
* mtdowling/cron-expression 

## Usage

### Basic usage
- Create php file like awscw-agent.php
``` php
<?php

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Sender as CWSender;
use AWSCustomMetric\CommandRunner;

try {
$diObj = new DI();
$diObj->setCommandRunner(new CommandRunner());
//Optional
//$diObj->setLogger(new DefaultLogger());

// Create the Sender
$cwSender = new CWSender("AWS_KEY", "AWS_SECRET", "AWS_REGION", new CommandRunner());
$cwSender->setNamespace('Custom/System');
$cwSender->addPlugin([
    new DiskUsage($diObj),
    new MemoryUsage($diObj)
]);
$cwSender->run();
} catch (\Exception $e) {
    //Error handling
}

// ...
```
- Add to cron like :
``` shell
*/10 * * * * /path/to/php /path/to/awscw-agent.php
```

### Auto Discover InstanceId
For AWS EC2 instances, some meta-data can be obtained from system like instance-id : 
``` shell
/usr/bin/wget -q -O - http://169.254.169.254/latest/meta-data/instance-id
```
While creating Sender object, if you dont give instance-id param, class tries to find instance-id using above cmd.

### Cron for Metric Plugins
Each metric plugin can be configured to run at specified time. Time can be defined at crontab format. For more info: https://github.com/mtdowling/cron-expression 
``` php
<?php

use AWSCustomMetric\DI;
use AWSCustomMetric\Logger\DefaultLogger;
use AWSCustomMetric\Sender as CWSender;
use AWSCustomMetric\CommandRunner;

try {
$diObj = new DI();
$diObj->setCommandRunner(new CommandRunner());
//Optional
//$diObj->setLogger(new DefaultLogger());

//metric will be sent at every sender->run calls
$diskPlugin = new DiskUsage($diObj, 'Appname/System', '* * * * *');

//metric will be sent at every hour
$memoryPlugin = new MemoryUsage($diObj, 'Appname/System', '0 * * * *');

// Create the Sender
$cwSender = new CWSender("AWS_KEY", "AWS_SECRET", "AWS_REGION", new CommandRunner());
$cwSender->setNamespace('Custom/System');
$cwSender->addPlugin([$diskPlugin, $memoryPlugin]);
$cwSender->run();
} catch (\Exception $e) {
    //Error handling
}

// ...
```

## Installation
You can use Composer to install :

``` shell
composer require fustundag/awscw-custom-metrics
```

## TODO
* ~~NOT TESTED.~~ ~~86% Coverage~~ 100% Coverage
* MORE PLUGINS.

## Contributing
You can contribute by forking the repo and creating pull requests. You can also create issues or feature requests.

## Disclaimer
Your AWS CloudWatch usage my be charged. Please check CloudWatch pricing page : https://aws.amazon.com/cloudwatch/pricing/

## License
This project is licensed under the MIT license. `LICENSE` file can be found in this repository.
