# CLI Task Handler

The CLI (command line interface) task handler allows for execution of PHP in the
standard shell instead of through a web server like Apache. Command line scripts
are structured as tasks, which are similar to controllers (task/action pattern).

An example task:

```php
<?php

namespace Vendor\MyNamespace\Tasks;

use Vectorface\SnappyRouter\Task\AbstractTask;

class DatabaseTask extends AbstractTask
{
    public function cleanupAction($cliParams)
    {
        // perform some database cleanup here
    }
}
```

The task can be registered with the router config:

```php
<?php

require_once 'vendor/autoload.php';

use Vectorface\SnappyRouter\Config\Config;

$config = new Config(array(
    Config::KEY_HANDLERS => array(
        'CliHandler' => array(
            Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\CliTaskHandler',
            Config::KEY_OPTIONS => array(
                Config::KEY_TASKS => array(
                    'DatabaseTask' => 'Vendor\\MyNamespace\\Tasks\\DatabaseTask'
                )
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

Suppose the above code is in a file named `router.php`. To execute the cleanup
action we can use the following command:

```shell
$> php router.php --task Database --action cleanup
```

