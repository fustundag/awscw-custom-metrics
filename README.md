# AWS CloudWatch Custom Metrics [![Build Status](https://travis-ci.org/fustundag/awscw-custom-metrics.svg?branch=master)](https://travis-ci.org/fustundag/awscw-custom-metrics)

You can send custom metrics to AWS CloudWatch like disk/memory usage

## Usage

### Basic usage
- Create php file like awscw-agent.php
``` php
<?php

use AWSCustomMetric\Sender as CWSender;

try {
// Create the Sender
$cwSender = new CWSender("AWS_KEY", "AWS_SECRET", "AWS_REGION");
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

## TODO
* NOT TESTED.

## Contributing
You can contribute by forking the repo and creating pull requests. You can also create issues or feature requests.

## Disclaimer
Your AWS CloudWatch usage my be charged. Please check CloudWatch pricing page : https://aws.amazon.com/cloudwatch/pricing/

## License
This project is licensed under the MIT license. `LICENSE` file can be found in this repository.
