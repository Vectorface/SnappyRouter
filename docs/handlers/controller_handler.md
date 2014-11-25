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

# Specifying Controllers in the Configuration

There are three ways to specify the how to resolve a controller in the
configuration. Controllers are listed in the `options` key within the handler.

## Explicit List of Controllers

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

## Registering a list of Controller Namespaces

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

## Registering a Folder of Controller PHP Files

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