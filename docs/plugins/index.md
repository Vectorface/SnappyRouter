# Plugins

SnappyRouter provides a basic plugin system allowing you to interject your own
code before the router hands off control and after the route has finished.

Note that to interrupt the standard route, the plugin method *must* throw an
exception. The return value of the plugin methods are not used.

## Writing your own Plugin

Plugins are very easy to implement, simply extend the class
`Vectorface\SnappyRouter\Plugin\AbstractPlugin` and implement the desired
methods.

Note that you *do not* need to implement all of the methods. You are free to
implement only the methods that you care about.

Example:

```
<?php

namespace Vendor\MyNamespace\Plugin;

use \Exception;
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

```
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

```
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

