# Rest Handler

The class `Vectorface\SnappyRouter\Handler\RestHandler` provides a simple
"by convention" api routing handler that builds on top of the
[controller handler](/handlers/controller_handler) by mapping specific route
patterns to controllers and actions.

## Rest Routing

The following route patterns are supported:

```
/(optional/base/path/)v{$apiVersion}/${controller}/${objectId}/${action}(/$additionalParameters...)
/(optional/base/path/)v{$apiVersion}/${controller}/${action}
/(optional/base/path/)v{$apiVersion}/${controller}/${objectId}
/(optional/base/path/)v{$apiVersion}/${controller}
```

Examples:

```
/api/v1.2/users/1234/save/relationships
/api/v1.2/users/1234/save
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

namespace Vendor/MyNamespace/Handler;

use \Exception;
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

