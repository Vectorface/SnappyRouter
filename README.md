#SnappyRouter
[![Build Status](https://travis-ci.org/Vectorface/SnappyRouter.svg?branch=master)](https://travis-ci.org/Vectorface/SnappyRouter)
[![Code Coverage](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Vectorface/SnappyRouter/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/Vectorface/Snappy-Router/v/stable.svg)](https://packagist.org/packages/Vectorface/Snappy-Router)
[![License](https://poser.pugx.org/Vectorface/Snappy-Router/license.svg)](https://packagist.org/packages/Vectorface/Snappy-Router)

SnappyRouter is a lightweight router written in PHP. The router offers features
standard in most other routers such as:
- Controller/Action based routes
- Pattern matching routes (powered by nikic/FastRoute)
- Direct file invokation (wrap paths to specific files through your router)
and makes it easy to write your own routing handler. The router is lightweight
and very flexible. It is designed to work with your existing "seasoned" codebase
and help with bringing code up to modern standards while maintaining backwards
compatibility and existing entry points.

## Feature Roadmap to 0.1 beta Release

TODO:
- Direct file invoke handler
- Automatic controller registration by namespace
- Automatic controller registration by folder
- Automatic config registration in the DI