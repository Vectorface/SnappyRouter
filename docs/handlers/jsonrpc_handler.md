# JSON-RPC 1.0 + 2.0 Handler

The class `Vectorface\SnappyRouter\Handler\JsonRpcHandler` provides a means of
calling class methods via the JSON-RPC protocol. Version 1.0 and 2.0 of the
protocol should both be fully supported.

Some items of interest about this implementation:
* Supports batch calls; Many calls in a single request.
* Notification calls; Drops responses without a request id.
* Handles both parameter arrays, and named parameters.
* Transparently handles both the JSON-RPC 1.0 and 2.0 spec.
* JSON-RPC 1.0 class hinting *is not supported*. (On purpose.)

## Why JSON-RPC?

JSON-RPC allows calling server-side methods on the client side nearly
transparently. The only limitations are generally limitations of JSON
serialization; PHP associative arrays become untyped JSON objects.

Put differently, a remote procedure call can be abstracted out to look almost
identical to a local method call, making it very easy to integrate server-side
calls into client-side or remote code.

API clients can also be simpler too because the local and remote method
signatures can be the same. There is no need to map URLs and/or parameters as in
REST APIs.

For example, one could expose the following class on the server:

```php
<?php

class Adder
{
	public function add($arg1, $arg2)
	{
		return $arg1 + $arg2;
	}
}
```

The Adder could be called locally as:
```php
$adder = new Adder();
$adder->add(1, 1); // 2!
```

Or it could be called remotely:
```php
$adder = new $myJsonRpcClient("http://.../Adder"); // Any JSON-RPC client.
$adder->add(1, 1); // 2!
```

## Usage

To expose the example Adder class listed in the previous section, one could
configure a router instance as follows:

```php
<?php

use Vectorface\SnappyRouter\Config\Config;

$config = new Config(array(
	Config::KEY_HANDLERS => array(
		'JsonRpcHandler' => array(
			Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\JsonRpcHandler',
			Config::KEY_OPTIONS => array(
				Config::KEY_SERVICES => array(
					'Adder' => '\Adder' // Adder, as above
				),
			)
		)
	)
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

If the router is called with a URI ending in "Adder(.php)" and a valid JSON-RPC
request POSTed for the method "add", the router should yield a JSON-RPC encoded
response with the sum of the two arguments.
