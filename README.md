# SnappyRouter

[![Build Status](https://travis-ci.org/Vectorface/SnappyRouter.svg?branch=master)](https://travis-ci.org/Vectorface/SnappyRouter)
[![Code Coverage](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/Vectorface/Snappy-Router/v/stable.svg)](https://packagist.org/packages/Vectorface/Snappy-Router)
[![License](https://poser.pugx.org/Vectorface/Snappy-Router/license.svg)](https://packagist.org/packages/Vectorface/Snappy-Router)

SnappyRouter is a lightweight router written in PHP. The router offers features
standard in most other routers such as:

- Controller/Action based routes
- Rest-like routes with API versioning
- Pattern matching routes (based off [nikic/FastRoute](https://github.com/nikic/FastRoute))
- Direct file invocation (wrap paths to specific files through the router)

SnappyRouter makes it easy to write your own routing handler for any imaginable
custom routing scheme.

*SnappyRouter is designed to work with your existing "seasoned"
codebase to provide a common entry point for your code base.* SnappyRouter is
ideal for existing projects that lack the features of a modern framework. By
providing a number of flexible different routing handlers, any PHP code base
can be retrofitted behind the router (usually) without requiring changes to
your existing code. For more information on why you want to use a router,
[see the documentation](https://snappyrouter.readthedocs.org/en/latest/#why-would-i-want-to-use-snappyrouter).

For more information, view the detailed [documentation](https://snappyrouter.readthedocs.org/en/latest/).
