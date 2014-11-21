# Direct Script Handler

The direct script handler maps web requests to raw PHP scripts. For PHP
applications that do not use the front controller pattern and do not have a
single entry point, this handler can be used to provide one without affecting
any of the current code.

## How to use it

The handler works by scanning the path for a specific prefix and matching it
to a locally stored folder.

For example we may have a folder structure like:

```
/home/user/
    webroot/
        index.html
        scripts/
            test_script.php
```

with our web server configured to use `/home/user/webroot` as the default
document root. Accessing the script directly would be done through a url like:

```
http://localhost/scripts/test_script.php
```

The handler can then be configured as such:

```php
<?php

use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\DirectScriptHandler;

$config = new Config(array(
    Config::KEY_HANDLERS => array(
        'DirectHandler' => array(
            Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handlers\\DirectScriptHandler',
            Config::KEY_OPTIONS => array(
                DirectScriptHandler::KEY_PATH_MAP => array(
                    '/scripts/' => '/home/user/webroot/scripts',
                    '/' => '/home/user/webroot/scripts'
                )
            ),
            Config::KEY_PLUGINS => array(
                // optional list of plugins to put in front of your scripts
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

## Path Map Fallback Strategy

Many application environments use virtual hosts, path aliases, symbolic
links, etc. The URL to a particular script may not always be obvious and
consistent across production, test environments, and local development
environments. For this reason, the `DirectScriptHandler::KEY_PATH_MAP` config
option supports multiple path prefixes which the handler will try each one
iteratively (in order) until it finds a matching script.

In the above example, we may have a virtual host pointing directly to the
scripts folder, and the above configuration would continue to work. For example:

```
http://livesite.example.com/test_script.php
```