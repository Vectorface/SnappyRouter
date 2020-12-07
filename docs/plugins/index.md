# Plugins

SnappyRouter provides a basic plugin system allowing you to interject your own
code before the router hands off control and after the route has finished.

Note that to interrupt the standard route, the plugin method *must* throw an
exception. The return value of the plugin methods are not used.

## Enabling Plugins

Plugins are specified in the configuration under options for each handler.
Plugins can be specified as an arbitrary key mapping to the name of the
class or as an array with fields for the file and class.

```php
...
    Config::KEY_HANDLERS => array(
        'MyHandler' => array(
            Config::KEY_CLASS => 'Vendor\\MyNamespace\\Handler\\MyCustomHandler',
            Config::KEY_OPTIONS => array(
                Config::KEY_PLUGINS => array(
                    'RouterHeaderPlugin' => 'Vectorface\\SnappyRouter\\Plugin\\HttpHeader\\RouterHeaderPlugin',
                    'MyCustomPlugin' => array(
                        Config::KEY_CLASS => '\MyCustomPlugin',
                        Config::KEY_FILE  => '/home/user/project/plugins/MyCustomPlugin.php'
                    )
                )
            )
        )
    )
...
```

## Writing your own Plugin

Plugins are very easy to implement, simply extend the class
`Vectorface\SnappyRouter\Plugin\AbstractPlugin` and implement the desired
methods.

Note that you *do not* need to implement all the methods. You are free to
implement only the methods that you care about.

Example:

```php
<?php

namespace Vendor\MyNamespace\Plugin;

use Exception;
use Vectorface\SnappyRouter\Handler\AbstractHandler;
use Vectorface\SnappyRouter\Plugin\AbstractPlugin;

class MyPlugin extends AbstractPlugin
{
    public function afterHandlerSelected(AbstractHandler $handler)
    {
        parent::afterHandlerSelected($handler);
        // perform some logic
    }

    public function afterFullRouteInvoked(AbstractHandler $handler)
    {
        parent::afterFullRouteInvoked($handler);
        // perform some logic
    }

    public function errorOccurred(AbstractHandler $handler, Exception $exception)
    {
        parent::errorOccurred($handler, $exception);
        // handle an error
    }
}
```

### `afterHandlerSelected`

The method `afterHandlerSelected` is invoked after the router has determined
the appropriate handler for the route but before the actual route is invoked.

This method provides an opportunity for initializing code before your actual
route is invoked or stopping the route entirely (by throwing an exception).

An example demonstrating interrupting a route based on custom logic.

```php
<?php
    ...
    public function afterHandlerSelected(AbstractHandler $handler)
    {
        if ($handler instanceof MyCustomHandler) {
            $authentication = $this->get('authentication');
            if ($authentication->isAuthorized() === false) {
                throw new UnauthorizedException('Authorization not valid.');
            }
        }
    }
    ...
```

### `afterFullRouteInvoked`

The method `afterFullRouteInvoked` is (unsurprisingly) invoked after the
router has invoked the route. This method is primarily to allow for clean up,
logging, etc.

An example demonstrating a specific call being logged.

```php
<?php
    ...
    public function afterFullRouteInvoked(AbstractHandler $handler)
    {
        if ($handler instanceof MyCustomHandler) {
            $request = $handler->getRequest();
            if ($request->getController() === 'SomeController' &&
                $request->getAction()     === 'SomeAction') {
                $this->get('logger')->log('SomeAction was invoked.');
            }
        }
    }
    ...
```

