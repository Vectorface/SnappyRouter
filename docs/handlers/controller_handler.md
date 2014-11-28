# Controller Handler

The controller handler provides the "C" part of the MVC application paradigm.
Controller actions become the entry point to your application. The router
provides the environment information through the parent controller class.

Controllers must follow the naming convention `"${NAME}Controller"` and actions
must follow the naming convention `"${NAME}Action". Actions can optionally
accept an array as an argument which will be populated with the remaining
path components.

SnappyRouter also integrates the popular
[Twig](http://twig.sensiolabs.org/) templating engine to provide the "V" part
of MVC, with a natural convention mapping template files to controller actions.

An example controller:

```php
<?php

namespace Vendor\MyNamespace\Controllers;

use Vectorface\SnappyRouter\Controller\AbstractController;

class ExampleController extends AbstractController
{
    public function indexAction($routeParams)
    {

    }

    public function loginAction($routeParams)
    {
        if ($this->request->isPost()) {
            // retrieve the username from the form trimmed
            $username = $this->request->getPost('username', '', 'trim');
            // retrieve the password from the form
            $password = $this->request->getPost('password', '');

            if (!empty($username) && !empty($password)) {
                // retrieve the authentication system from the DI layer
                $authService = $this->get('authenticationService');
                if ($authService->isValidCredentials($username, $password)) {
                    // login was successful so redirect the user
                    header('location: /some/location');
                    exit();
                } else {
                    // pass the login errors back to the template
                    return array(
                        'loginErrors' => $authService->getErrors()
                    );
                }
            }
            // pass the login errors back to the template
            return array(
                'loginErrors' => array('Missing username or password.')
            );
        }
    }
}
```

and an example configuration for this handler:

```php
<?php

use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

$config = new Config(array(
    Config::KEY_HANDLERS => array(
        'MvcHandler' => array(
            Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\ControllerHandler',
            Config::KEY_OPTIONS => array(
                Config::KEY_NAMESPACES => array(
                    'Vendor\\MyNamespace\\Controllers'
                ),
                ControllerHandler::KEY_VIEWS => array(
                    ControllerHandler::KEY_VIEWS_PATH => '/home/user/project/views'
                )
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

## Controller Routing

The controller handler uses a simple convention for mapping routes to
controllers and actions.

```
/(optional/base/path/)${controller}/${action}/(param1/)(param2)
```

After stripping the base path prefix, the first path component is assumed to be
the controller and second path component is assumed to be the action. Any
subsequent path components are assumed to be route parameters to be passed to
the controller action.

- If the action is missing in the path the router will fall back to `indexAction`
  by default.
- If the controller is missing in the path, the router will fall back to
  `IndexController`.


The base path is specified in the handler options like such:

```php
    ...
    Config::KEY_OPTIONS => array(
        ControllerHandler::KEY_BASE_PATH => '/my/base/path';
        ...
    ),
    ...
```

## Specifying Controllers in the Configuration

There are three ways to specify the how to resolve a controller in the
configuration. Controllers are listed in the `options` key within the handler.

### Explicit List of Controllers

The list of controllers can be explicitly given as a key/value pair. The key for
the controller must match the convention `"${NAME}Controller"` and the value
must be a valid PHP class or an array with specific fields (depending on whether
your controllers are namespaced).

Example for namespaced controllers:

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_CONTROLLERS => array(
            'ExampleController' => 'Vendor\\MyNamespace\\Controllers\\ExampleController',
            'AnotherController' => 'Vendor\\MyNamespace\\Controllers\\AnotherController',
            ...
        )
    ),
    ...
```

Example for non namespaced controllers:

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_CONTROLLERS => array(
            'ExampleController' => array(
                Config::KEY_CLASS => '\ExampleController',
                Config::KEY_FILE  => '/home/user/project/controllers/ExampleController.php'
            )
        )
    ),
    ...
```

### Registering a list of Controller Namespaces

If your code is namespaced, you can register a list of namespaces for
SnappyRouter to use to autodetect the appropriate controller class.

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_NAMESPACES => array(
            'Vendor\\MyNamespace\\Controllers',
            'Vendor\\AnotherNamespace\\Controllers',
            ...
        )
    ),
    ...
```

The namespaces will be scanned in the order listed in the array.

### Registering a Folder of Controller PHP Files

If your code is not namespaced, you can give SnappyRouter a list of folders
to check (recursively) for a PHP file matching `${NAME}Controller.php`.

```php
    ...
    Config::KEY_OPTIONS => array(
        Config::KEY_FOLDERS => array(
            '/home/user/project/app/controllers',
            '/home/user/project/app/moreControllers',
            ...
        )
    ),
    ...
```

## Getting Request Details

The controller handler automatically registers request information in the
`$request` property of the class
`Vectorface\SnappyRouter\Controller\AbstractController`. As long as your
controller subclasses this class, you can access the current request through
`$this->request`. The request object is an instance of
`Vectorface\SnappyRouter\Request\HttpRequest`.


Example demonstrating how to check if the request made was a `POST` request
and to get the variables from the form.


```php
<?php

    ...
    public function myAction($routeParams)
    {
        if (false === $this->request->isPost()) {
            throw new MethodNotAllowedException(
                'Only POST requests allowed.',
                array('POST')
            );
        }

        // get the object ID typecasted to an integer
        // if not found, we get back a default value of 0
        $objectId = max(0, $this->request->getPost('id', 0, 'int'));
        if (0 === $objectId) {
            throw new Exception('Invalid Object ID');
        }

        // handle stuff
        ...
        return array('MyObject' => $object');
    }
    ...
```

### Retrieving and Filtering Inputs

The request object can retrieve the values of query and post parameters and
provides the option to specify default values if the parameters are not found.
Furthermore, you can specify a convenient list of filters to apply against the
value.

Example retrieving a simple field `id` from both the query and post parameters:

```php
    ...
    // retrieve the ID from the query parameters
    $id = $this->request->getQuery('id');
    // retrieve the ID from the post parameters
    $id = $this->request->getPost('id');
    ...
```

Example retrieving the field `id` from the query parameters and specifying a
default value to return if the parameter is missing:

```php
    ...
    // retrieve the ID from the query parameters or use 0
    $id = $this->request->getQuery('id', 0);
    ...
```

Example retrieving the field `id` and applying the `int` filter to type cast
the value to an integer:

```php
    ...
    // retrieve the ID from the query parameters and ensure it is a positive
    // integer
    $id = max(0, $this->request->getQuery('id', 0, 'int'));
    // alternatively the filters can be specified as an array
    $id = max(0, $this->request->getQuery('id', 0, array('int')));
    ...
```

Filters can also be combined as an array. An example of a string that is
trimmed and fully converted to lower case.

```php
    ...
    // usernames are trimmed and stored in lower case for case insensitivity
    $username = $this->getPost('username', '', array('trim', 'lower'));
    ...
```

### List of Valid Filters

- `int` - Type casts the value as an integer.
- `float` - Type casts the value as a float.
- `trim` - Trims leading and trailing whitespace from the string.
- `lower` - Converts the entire string to lower case.
- `upper` - Converts the entire string to upper case.
- `squeeze` - Condense a multi-line string by removing any empty lines or lines
              containing only whitespace.

## Additional Route Parameters

Any additional route parameters will be passed as an array to the action
method.

For example, a route path like:

```
/example/demo/1234/perform
```

will be routed to the `ExampleController` and `demoAction` method with the
argument `array('1234', 'perform')`.

## Integration with Twig

For convenience, SnappyRouter provides optional integration with the Twig
template engine. A simple convention provides an automatic mapping between the
controller action and the view. Manual view rendering is also made easy.

### Convention-based Twig Integration

For the convention-based method, simply provide a Twig `view` folder in the
view configuration.

```php
<?php

use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

$config = new Config(array(
    Config::KEY_HANDLERS => array(
        'MyHandler' => array(
            Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\ControllerHandler',
            Config::KEY_OPTIONS => array(
                ControllerHandler::KEY_VIEWS => array(
                    ControllerHandler::KEY_VIEWS_PATH => '/path/to/views/folder'
                )
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

The view folder must follow the convention:

```
/path/to/views/folder
    /controller1
        /action1.twig
        /action2.twig
    /controller2
        /action1.twig
        /action2.twig
```

For example, given the controller `ExampleController` and the action
`helloworldAction` we may have a view structure like:

```
/home/user/project
    /views
        /example
            helloworld.twig
```

Note that the filename of your Twig template must match `${ACTION}.twig`.

To pass variables to the Twig template, you can add additional fields to the
associative array `$this->viewContext` inside your controller action.

For example:

```php
    ...
    public function loginAction()
    {
        $this->viewContext['username'] = 'Fred';
    }
    ...
```

and your `login.twig` template can use the variable `username`:

```
Hello {{ username|e }}.
```

Alternatively, if a controller action returns an associative array, the keys
and values get bound to variables in the view. For example:


```php
    ...
    public function loginAction()
    {
        return array('username' => 'Fred');
    }
    ...
```

### Using Twig Manually

The class `Vectorface\SnappyRouter\Controller\AbstractController` provides an
easy to use method (`renderView`) for rendering Twig templates directly. The
method takes an array of a variables to pass to the view and a string indicating
which template to render.

For example:

```php
    ...
    public function loginAction()
    {
        return $this->renderView(
            array('username' => 'Fred'),
            'login.twig'
        );
    }
    ...
```

*Note that Twig always requires a base views folder in the configuration and all
templates are specified relative to the base folder.*
