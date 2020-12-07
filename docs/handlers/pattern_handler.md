# Pattern Match Handler

Direct pattern matching in SnappyRouter is supported by the pattern match
handler. Similar to many other popular routers, the routing configuration is
specified as a list of regular expression patterns mapping to callback
functions.

An example configuration:

```php
<?php

use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\PatternMatchHandler;

$config = new Config([
    Config::KEY_HANDLERS => [
        'PatternHandler' => [
            Config::KEY_CLASS => PatternMatchHandler::class,
            Config::KEY_OPTIONS => [
                PatternMatchHandler::KEY_ROUTES => [
                    '/users/{name}/{id:[0-9]+}' => [
                        'get' => function ($routeParams) {
                            // invoked only for GET calls
                        },
                        'post' => function ($routeParams) {
                            // invoked only for POST calls
                        }
                    ],
                    '/users' => function($routeParams) {
                        // invoked for all HTTP verbs
                    }
                ]
            ]
        ]
    ]
]);
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

## Specifying Routes

Routes are listed as arrays using regular expressions with named parameters. For the documentation on the individual patterns see the
[FastRoute library](https://github.com/nikic/FastRoute). The routes must be
specified in the options of the handler.

The patterns should be listed as the keys to the array and must map to a
`callable` function or another array with HTTP verbs as keys.

### Examples

A route using the same callback for all HTTP verbs.

```php
    ...
    PatternMatchHandler::KEY_ROUTES => [
        '/api/{version}/{controller}/{action}' => function ($routeParams) {
            // invoked for all HTTP verbs
        }
    ],
    ...
```

A route specifying individual HTTP verbs.

```php
    ...
    PatternMatchHandler::KEY_ROUTES => [
        '/api/{version}/{controller}/{action}' => [
            'get' => function ($routeParams) {
                // handle GET requests
            },
            'post' => function ($routeParams) {
                // handle POST requests
            },
            'put' => function ($routeParams) {
                // handle PUT requests
            },
            'delete' => function ($routeParams) {
                // handle DELETE requests
            },
            'options' => function ($routeParams) {
                // handle OPTIONS requests
            },
            'head' => function ($routeParams) {
                // handle HEAD requests
            }
        ]
    ],
    ...
```
