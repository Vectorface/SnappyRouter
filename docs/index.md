# SnappyRouter

SnappyRouter is a lightweight router written in PHP. The router offers features
standard in most other routers such as:

- Controller/Action based routes
- Rest-like routes with API versioning
- Pattern matching routes (based off [nikic/FastRoute](https://github.com/nikic/FastRoute))
- Direct file invokation (wrap paths to specific files through the router)

SnappyRouter makes it easy to write your own routing handler for any imaginable
custom routing scheme. It is designed to work with your existing "seasoned"
codebase to provide a common entry point for your code base.

## What makes SnappyRouter unique?

SnappyRouter is very fast and flexible. The router can be put in front of
existing PHP scripts with very little noticeable overhead. The core design of
the router means it gets out of the way quickly and executes your own code as
soon as possible. *You should be able to add SnappyRouter to your existing
project without modifying any existing code*.

SnappyRouter supports PHP 5.3, 5.4, 5.5, and 5.6, as well as HHVM.

## Why would I want to use SnappyRouter?

Modern best practices in PHP applications has lead to the so-called
[front controller pattern](https://en.wikipedia.org/wiki/Front_Controller_pattern)
(a single entry point to your application). The benefits of a single entry
point include:

- Better flexibility over global initialization and shut down (say goodbye to
    "global.php", "common.php" and auto_prepend_file directives).
- Easier to manage code base due to each entry point not needing to include
    global setup code.
- A more consistent project code base (your project is no longer a collection
    of related PHP scripts).
- Flexible pretty URLs (great for SEO and application UX).
