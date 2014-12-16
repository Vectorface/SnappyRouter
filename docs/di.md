# Dependency Injection

SnappyRouter provides a dependency injection (DI) layer for convenience and to
improve code testability. At its core, the DI layer is simply a key/value pair
matching strings to services. By providing your class dependencies using DI
instead of direct instantiation, you can control the inner workings of a
method at runtime through the use of mock and stub objects.

DI also allows for a single point of instantiation for commonly used services
such as CURL wrappers, mailers, database adapters, etc.

## Adding and Retrieving Services

Services can be added to the DI layer either directly or as a callback. It is
recommended to use a callback so that instantiation of the service can be
delayed until needed.

Example:

```php
<?php
    ...
    $di = Vectorface\SnappyRouter\Di\Di::getDefault();
    $di->set('database', function(Vectorface\SnappyRouter\Di\Di $di) {
        return new \PDO(
            'mysql:dbname=database;host=127.0.0.1',
            'username',
            'password'
        );
    });
    $db = $di->get('database');
    ...
```

## Specifying the DI Layer

The SnappyRouter configuration allows for specifying a default DI class. This
class can be your own code (subclassing the built-in class
`Vectorface\SnappyRouter\Di\Di`). For example:

```php
<?php

namespace Vendor\MyNamespace\Di;

use Vectorface\SnappyRouter\Di\Di;

class MyDi extends Di
{
    public function __construct()
    {
        parent::__construct(array(
            ...
            'database' => function(Di $di) {
                return new \PDO(
                    'mysql:dbname=database;host=127.0.0.1',
                    'username',
                    'password'
                );
            },
            ...
        ));
    }
}
```

Next specify the DI class in the configuration.

```php
<?php

use Vectorface\SnappyRouter\Config\Config;

$config = new Config(array(
    Config::KEY_DI => 'Vendor\\MyNamespace\\Di\\MyDi',
    Config::KEY_HANDLERS => array(
        ...
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

## Bootstrapping the DI Layer

SnappyRouter also provides a default DI class that can be configured when your
application bootstraps. For example:


```php
<?php

use Vectorface\SnappyRouter\Config\Config;

$config = new Config(array(
    ...
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);

// configure the DI manually
$di = Vectorface\SnappyRouter\Di\Di::getDefault();
$di->set('database', function ($di) {
    // return the database
})->set('mailer', function($di) {
    // return the mailer
})->set('...', function($di) {
    ...
});

echo $router->handleRoute();
```
## Using the Built-in DI Layer

Many of the classes provided by SnappyRouter also provide direct access to the
DI layer for convenience. The interface
`Vectorface\SnappyRouter\Di\DiProviderInterface` provides the two key methods
`get` and `set`. This interface is implemented by popular classes such as
`AbstractController`, `AbstractTask`, `AbstractPlugin`, etc.

Here is an example with a custom controller that has access to the DI layer
by simply extending `AbstractController`.

```php
<?php

namespace Vendor\MyNamespace\Controllers;

use Vectorface\SnappyRouter\Controller\AbstractController;

class MyCustomController extends AbstractController
{
    public function myAction()
    {
        // set this method as the last one called in the DI
        $this->set('lastMethodCalled', __METHOD__);

        $database = $this->get('database'); // retrieve the database from the DI layer
        // do something with $database
    }
}
```
