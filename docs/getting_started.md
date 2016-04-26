# Getting Started

In this tutorial, we will build a new application from scratch using
SnappyRouter.

The full tutorial application can be found
[here](https://github.com/Vectorface/SnappyTutorial).

## Creating the Project Structure

We begin by creating the project folder and recommended subfolders.

```shell
$> mkdir tutorial tutorial/app tutorial/public
$> mkdir tutorial/app/Controllers tutorial/app/Views tutorial/app/Views/index tutorial/app/Models
$> cd tutorial
```

The folder structure should look like this:

```
tutorial/
    app/
        Controllers/
        Models/
        Views/
            index/
    public/
```

## Redirects, Composer and index.php

We will use .htaccess files to redirect all incoming requests to a single entry
point in our application (`public/index.php`).

Create the following files:

```
#/tutorial/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule  ^$ public/    [L]
    RewriteRule  (.*) public/$1 [L]
</IfModule>
```

```
#/tutorial/public/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

We will also make use of Composer to provide dependencies and to autoload our
application classes. If you do not have Composer installed, follow the
documentation at [getcomposer.org](https://getcomposer.org/doc/00-intro.md).

Create the file `tutorial/composer.json` with the following contents:

```json
{
    "name": "vectorface/snappy-tutorial",
    "autoload": {
        "psr-4": {
            "Vectorface\\SnappyTutorial\\": "./app"
        }
    },
    "require": {
        "php": ">=5.3.0",
        "vectorface/snappy-router": "dev-master"
    }
}
```

and run

```shell
$> composer install
```

and finally the contents of `public/index.php`.

```php
<?php // public/index.php

require_once __DIR__.'/../vendor/autoload.php';

use Vectorface\SnappyRouter\Config\Config;
use Vectorface\SnappyRouter\Handler\ControllerHandler;

$config = new Config(array(
    Config::KEY_DI => 'Vectorface\\SnappyTutorial\\Models\\TutorialDi',
    Config::KEY_HANDLERS => array(
        'PageHandler' => array(
            Config::KEY_CLASS => 'Vectorface\\SnappyRouter\\Handler\\ControllerHandler',
            Config::KEY_OPTIONS => array(
                Config::KEY_NAMESPACES => 'Vectorface\\SnappyTutorial\\Controllers',
                ControllerHandler::KEY_BASE_PATH => '/tutorial',
                ControllerHandler::KEY_VIEWS => array(
                    ControllerHandler::KEY_VIEWS_PATH => realpath(__DIR__.'/../app/Views')
                )
            )
        )
    )
));
$router = new Vectorface\SnappyRouter\SnappyRouter($config);
echo $router->handleRoute();
```

For simplicity we include the configuration settings directly in
`public/index.php`. It is probably a better practice to store these settings
in a separate config file and include it in the index. For example:

```php
...
$configArray = require_once __DIR__.'/../app/config.php';
$config = new Config($configArray);
...
```
*N.B.* Any file placed in the public folder will be directly accessible through
the web browser. This folder should be used for any web assets (javascript, images,
css, fonts) or direct PHP scripts you wish to expose. Any script exposed through
the public folder will *not* be run through SnappyRouter.

## Setting up the DI Container

Dependency injection (DI) is a powerful tool for injecting services and
dependencies across your application at runtime. Some common examples include
the database adapter, cache adapters, mail senders, etc.

For this tutorial, we specify a class to use for DI. Create the file
`app/Models/TutorialDi.php` with the following contents:

```
<?php // app/Models/TutorialDi.php

namespace Vectorface\SnappyTutorial\Models;

use Vectorface\SnappyRouter\Di\Di;

class TutorialDi extends Di
{

    public function __construct()
    {
        parent::__construct($this->getDiArray());
    }

    protected function getDiArray()
    {
        return array(
            'projectTitle' => function(Di $di) {
                return 'SnappyRouter Tutorial';
            }
        );
    }
}
```

This container registers only the `projectTitle` key.

## Controllers and Views

We will setup an `IndexController` that extends our own abstract controller.
It is good practice to always include your own base controller on top of
`Vectorface\SnappyRouter\Controller\AbstractController` to provide common logic
across all your controllers.

The `BaseController` implements the `initialize` method which is invoked by
SnappyRouter before any action is invoked. Note that we retrieve the
`projectTitle` from the DI layer and hand it off to the view.

```php
<?php // app/Controllers/BaseController.php

namespace Vectorface\SnappyTutorial\Controllers;

use Vectorface\SnappyRouter\Controller\AbstractController;
use Vectorface\SnappyRouter\Handler\AbstractRequestHandler;
use Vectorface\SnappyRouter\Request\HttpRequest;

abstract class BaseController extends AbstractController
{

    public function initialize(HttpRequest $request, AbstractRequestHandler $handler)
    {
        parent::initialize($request, $handler);
        $this->viewContext['projectTitle'] = $this->get('projectTitle');
    }
}
```
And the `IndexController`:

```php
<?php // app/Controllers/IndexController.php

namespace Vectorface\SnappyTutorial\Controllers;

class IndexController extends BaseController
{
    public function indexAction()
    {
        return array(
            'content' => 'Hello SnappyRouter!'
        );
    }
}
```

Note that there are many ways to pass variables to the view.

1. Using the associative array `$this->viewContext` provided by
   `Vectorface\SnappyRouter\Controller\AbstractController`.
2. Returning an associative array (this array will be merged with
   `$this->viewContext`).
3. Directly rendering the view with `$this->renderView`. More details for this
   method can be found [here](handlers/controller_handler/#integration-with-twig).

We will divide our view into two files. The first file `app/Views/layout.twig` will
provide common boilerplate that we could reuse across multiple pages.

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ projectTitle|e }}</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    {% block content %}
    {% endblock %}
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
</body>
</html>
```

And a very simple view for our `indexAction` in `app/Views/index/index.twig`:

```html
{% extends 'layout.twig' %}

{% block content %}
<div class="container">
    <h1>{{ content }}</h1>
</div>
{% endblock %}
```

Once you add the `tutorial` folder to your standard web root, you should have
a working application at `http://localhost/tutorial/`.
