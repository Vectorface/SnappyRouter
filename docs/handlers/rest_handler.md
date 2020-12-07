# Rest Handler

The class `Vectorface\SnappyRouter\Handler\RestHandler` provides a simple
"by convention" api routing handler that builds on top of the
[controller handler](handlers/controller_handler) by mapping specific route
patterns to controllers and actions.

## Rest Routing

The following route patterns are supported:

```
/(optional/base/path/)v{$apiVersion}/${controller}/${objectId}/${action}
/(optional/base/path/)v{$apiVersion}/${controller}/${action}/${objectId}
/(optional/base/path/)v{$apiVersion}/${controller}/${action}
/(optional/base/path/)v{$apiVersion}/${controller}/${objectId}
/(optional/base/path/)v{$apiVersion}/${controller}
```

Examples:

```
/api/v1.2/users/1234/details
/api/v1.2/users/details/1234
/api/v1.2/users/search
/api/v1.2/users/1234
/api/v1.2/users
```

## JSON Serialization

Unlike the Twig view handler used in standard controller handler, the rest
handler is configured by default to encode all responses in JSON text. To
use a different encoder it is recommended to subclass the `RestHandler` class
and override a couple of methods.

Example:

```php
<?php

namespace Vendor\MyNamespace\Handler;

use Exception;
use Vectorface\SnappyRouter\Handler\RestHandler;

class MyRestHandler extends RestHandler
{
    public function getEncoder()
    {
        // return a custom encoder
    }

    public function handleException(Exception $e)
    {
        // custom exception handling if needed
    }
}
```

## Writing a Restful Controller

Similar to the [controller handler](handlers/controller_handler), the
controller class should subclass
`Vectorface\SnappyRouter\Controller\AbstractController`. A key difference
between the REST handler and the controller handler is that the route
parameters will always have the API version as the first element. If present
in the route, the `${objectId}` will be second element of the route parameters.

Note that the return value of the action will be encoded as a JSON string
automatically.

Example controller:
```php
<?php

namespace Vendor\MyNamespace\Controller;

use Exception;
use Vectorface\SnappyRouter\Controller\AbstractController;

class RestUsersController extends AbstractController
{
    public function detailsAction($routeParams)
    {
        $apiVersion = array_pop($routeParams);
        if (empty($routeParams)) {
            throw new Exception('Missing user ID');
        }
        $user = ModelLayer::getUser(array_pop($routeParams));
        return $user->getDetails();
    }
}
```
