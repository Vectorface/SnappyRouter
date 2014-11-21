# Handlers

ServiceRouter uses a number of different route handlers to serve web and CLI
requests. Handlers are a core component in the router and are the starting point
for customizing the router for your own application.

## What is a Handler?

The router makes very few assumptions about the executing environment (command
line, web server, etc). To handle each environment differently, the router
passes off control to a handler. The handler is responsible for most of the
routing logic.

## Built-in Handlers

ServiceRouter provides a number of built in routing handlers.

### Controller Handler

The controller handler provides the "VC" part of MVC. The router assumes your
web requests are searching for a pattern such as
`/prefix/controller/action/param1/param2` and will attempt to find the
appropriate controller and action to invoke. Furthermore, the Twig view engine
can be initialized to provide an easy to use controller-view binding.

[More details](controller_handler.md)

### Rest Handler

The rest handler provides REST-like API urls such as
`/api/v1.2/resource/1234` and extends the Controller Handler to route to an
appropriate controller. Responses are encoded as JSON by default.

[More details](rest_handler.md)

### Pattern Match Handler

The pattern match handler uses the powerful
[FastRoute](https://github.com/nikic/FastRoute) library to allow custom routes
to map directly to a callable function. This handler will seem familiar to users
of [Silex](http://silex.sensiolabs.org/) or [Express.js](http://expressjs.com/).

[More details](pattern_handler.md)

### Direct Script Handler

This handler allows the route to fall through to an existing PHP script. If your
application entry points are paths to scripts directly this handler can be used
to wrap access to those scripts through the router.

[More details](direct_handler.md)

### CLI Task Handler

This handler provides a command line entry point for tasks.

[More details](cli_handler.md)

## Writing your own Handler

Every application has unique conventions and workflows. ServiceRouter handlers
are very easy to extend and build your own to handle any custom routing your
application may need.

To begin, add a class that extends one of the abstract handler classes. For a
web request handler, it is recommended to extend
`Vectorface\\SnappyRouter\\Handler\\AbstractRequestHandler`.

```php
<?php

namespace Vendor\MyNamespace\Handler\MyCustomHandler;

use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;

class MyCustomHandler extends AbstractRequestHandler
{
    /**
     * Returns true if the handler determines it should handle this request and false otherwise.
     * @param string $path The URL path for the request.
     * @param array $query The query parameters.
     * @param array $post The post data.
     * @param string $verb The HTTP verb used in the request.
     * @return boolean Returns true if this handler will handle the request and false otherwise.
     */
    public function isAppropriate($path, $query, $post, $verb)
    {
        // apply some logic using the parameters to determine whether the
        // handler is appropriate for this request
        return true;
    }

    /**
     * Performs the actual routing.
     * @return mixed Returns the result of the route.
     */
    public function performRoute()
    {
        // perform the actual routing logic here
    }
}
```

and specify the handler in your config:

```php
<?php

use Vectorface\SnappyRouter\Config\Config;

$config = new Config(array(
    Config::KEY_HANDLERS => array(
        'MyHandler' => array(
            Config::KEY_CLASS => 'Vendor\\MyNamespace\\Handler\\MyCustomHandler',
            Config::KEY_OPTIONS => array(
                // an array of options
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```