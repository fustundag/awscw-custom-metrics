# AWS CloudWatch Custom Metrics [![Build Status](https://travis-ci.org/fustundag/awscw-custom-metrics.svg?branch=master)](https://travis-ci.org/fustundag/awscw-custom-metrics)

You can send custom metrics to AWS CloudWatch like disk/memory usage

## Usage

### Basic usage
- Create php file like awscw-agent.php
``` php
<?php

use AWSCustomMetric\Sender as CWSender;
use AWSCustomMetric\CommandRunner;

try {
// Create the Sender
$cwSender = new CWSender("AWS_KEY", "AWS_SECRET", "AWS_REGION", new CommandRunner());
$cwSender->setNamespace('Custom/System');
$cwSender->addPlugin(['DiskUsage', 'MemoryUsage']);
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

## TODO
* ~~NOT TESTED.~~ 86% Coverage
* MORE PLUGINS.

## Contributing
You can contribute by forking the repo and creating pull requests. You can also create issues or feature requests.

## Disclaimer
Your AWS CloudWatch usage my be charged. Please check CloudWatch pricing page : https://aws.amazon.com/cloudwatch/pricing/

## License
This project is licensed under the MIT license. `LICENSE` file can be found in this repository.
