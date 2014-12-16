# CLI Task Handler

The CLI (command line interface) task handler allows for execution of PHP in the
standard shell instead of through a web server like Apache. Command line scripts
are structured as tasks, which are similar to controllers (task/action pattern).

Tasks must follow the naming convention `"${NAME}Task"`. Actions do not require
any naming convention. Actions can (optionally) take an array as an argument
which will be populated with any additional command line options passed to the
script.

An example task:

```php
<?php

namespace Vendor\MyNamespace\Tasks;

use Vectorface\SnappyRouter\Task\AbstractTask;

class DatabaseTask extends AbstractTask
{
    public function cleanup($cliParams)
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

# Specifying Tasks in the Configuration

There are three ways to specify the list of tasks in the configuration. Tasks
are listed in the `options` key within the handler.

## Explicit List of Tasks

The list of tasks can be explicitly listed as a key/value pair. The key for the
task must match the convention `"${NAME}Task"` and the value must be valid
PHP class.

Example:

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_TASKS => array(
            'DatabaseTask' => 'Vendor\\MyNamespace\\Tasks\\DatabaseTask',
            'EmailTask'    => 'Vendor\\MyNamespace\\Tasks\\SendEmailTask',
            ...
        )
    ),
    ...
```

## Registering a list of Task Namespaces

If your code is namespaced, you can register a list of namespaces for
SnappyRouter to use to autodetect the appropriate task class.

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_NAMESPACES => array(
            'Vendor\\MyNamespace\\Tasks',
            'Vendor\\AnotherNamespace\\Tasks',
            ...
        )
    ),
    ...
```

The namespaces will be scanned in the order listed in the array.

## Registering a Folder of Task PHP Files

If your code is not namespaced, you can give SnappyRouter a list of folders
to check (recursively) for a PHP file matching `${NAME}Task.php`.

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_FOLDERS => array(
            '/home/user/project/app/tasks',
            '/home/user/project/app/moretasks',
            ...
        )
    ),
    ...
```
