# bitexpert/pathfinder
A PHP routing component.

[![Build Status](https://travis-ci.org/bitExpert/pathfinder.svg?branch=master)](https://travis-ci.org/bitExpert/pathfinder)
[![Coverage Status](https://coveralls.io/repos/github/bitExpert/pathfinder/badge.svg?branch=master)](https://coveralls.io/github/bitExpert/pathfinder?branch=master)

Router
------

The router is responsible for resolving the target from the given route as well building an uri for a given 
route identifier (and it`s parameters). Using the [Psr7Router](src/bitExpert/Pathfinder/Psr7Router.php) is pretty easy:

```php
$router = new \bitExpert\Pathfinder\Psr7Router();
$router->setRoutes(
    [
        new Route(['GET'], '/', 'index'),
        new Route(['GET'], '/question/[:title]_[:id]', 'question'),
        new Route(['GET', 'POST'], '/editquestion', 'editquestion')
    ]
);
```

Routes
------
[Routes](src/bitExpert/Pathfinder/Route.php) in Pathfinder are implemented immutual. You may define your routes by creating a route instance
directly as shown above or using the [RouteBuilder](src/bitExpert/Pathfinder/RouteBuilder.php) for convenience (recommended):

```php
use bitExpert\Pathfinder\RouteBuilder;
use bitExpert\Pathfinder\Matcher\NumericMatcher;

RouteBuilder::route()
    ->get('/')
    ->to('home')
    ->build();

RouteBuilder::route()
    ->from('/user')
    ->accepting('POST')
    ->accepting('PUT')
    ->to('userAction')
    ->build();

RouteBuilder::route()
    ->get('/')
    ->to(function () {

    })
    ->named('home')
    ->build();

RouteBuilder::route()
    ->get('/user/[:userId]')
    ->to('userAction')
    ->ifMatches('userId', new NumericMatcher())
    ->build();
```

Customizing the RouteBuilder
----------------------------
In case you need some special route classes for your application, you may configure
the [RouteBuilder](src/bitExpert/Pathfinder/RouteBuilder.php) to use your custom route class instead of
[Route](src/bitExpert/Pathfinder/Route.php) either for a particular route:

```php
$route = RouteBuilder::route(MyCustomRoute::class)
    ->get('/')
    ->to('home')
    ->build();

$route->doCustomStuffDefinedInYourClass();
```

or globally as default class to use:

```php
RouteBuilder::setDefaultRouteClass(MyCustomRoute::class);

$route = RouteBuilder::route()
    ->get('/')
    ->to('home')
    ->build();

$route->doCustomStuffDefinedInYourClass();
```

Matchers
--------
[Matchers](src/bitExpert/Pathfinder/Matcher/Matcher.php) are used to ensure that your route params match given criteria such as digits only:

```php
RouteBuilder::route()
    ->get('/user/[:userId]')
    ->to('userAction')
    ->ifMatches('userId', new NumericMatcher())
    ->build();
```

You may add several matchers for one param, just by adding them via:

```php
RouteBuilder::route()
    ->get('/user/[:userId]')
    ->to('userAction')
    ->ifMatches('userId', new NumericMatcher())
    ->ifMatches('userId', new MyCustomMatcher())
    ->ifMatches('userId', function ($value) {

    })
    ->build();
```

Middleware
----------
You may use the [BasicRoutingMiddleware](src/bitExpert/Pathfinder/Middleware/BasicRoutingMiddleware.php) to integrate Pathfinder into your PSR-7 compatible project:

```php
use bitExpert\Pathfinder\Route;
use bitExpert\Pathfinder\Psr7Router;
use bitExpert\Pathfinder\Middleware\BasicRoutingMiddleware;

$router = new Psr7Router();
$router->addRoute(RouteBuilder::route()->get('/')->to('home')->build());

// The routing middleware will use the given router to match the request and will set the routing result as value
// of the request attribute named 'routingResult' for further use
$routingMiddleware = new BasicRoutingMiddleware($router, 'routingResult');
```
 
Errors
------
The [Psr7Router](src/bitExpert/Pathfinder/Psr7Router.php) does not throw an Exception if the request doesn't match any route.
It will still return a [RoutingResult](src/bitExpert/Pathfinder/RoutingResult.php) returning true when calling $routingResult->failed();
You may receive the failure reason by calling $routingResult->getFailure() which will give you information about
the failure reason and an optional route which could have matched, but didn't fulfill all criteria:

```php
use bitExpert\Pathfinder\Psr7Router;
use bitExpert\Pathfinder\RouteBuilder;
use Zend\Diactoros\ServerRequest;
use bitExpert\Pathfinder\Matcher\NumericMatcher;

$router = new Psr7Router();

$homeRoute = RouteBuilder::route()
    ->get('/')
    ->to('home')
    ->build();

$orderUpdateRoute = RouteBuilder::route()
    ->put('/order/[:orderId]')
    ->to('updateOrder')
    ->ifMatches('id', new NumericMatcher())
    ->build();
 
$router->setRoutes([$homeRoute, $orderUpdateRoute]);


// Not found example
$request = new ServerRequest([], [], '/users', 'GET');
$result = $router->match($request);

$result->failed(); // -> true
$result->getFailure(); // -> RoutingResult::FAILED_NOT_FOUND
$result->hasRoute(); // -> false
 
// Method not allowed example
$request = new ServerRequest([], [], '/order/1', 'GET');
$result = $router->match($request);

$result->failed(); // -> true
$result->getFailure(); // -> RoutingResult::FAILED_METHOD_NOT_ALLOWED
$result->hasRoute(); // -> true
$result->getRoute(); // -> $orderUpdateRoute
 
 
// BadRequest example
$request = new ServerRequest([], [], '/order/abc', 'PUT');
$result = $router->match($request);

$result->failed(); // -> true
$result->getFailure(); // -> RoutingResult::FAILED_BAD_REQUEST
$result->hasRoute(); // -> true
$result->getRoute(); // -> $orderUpdateRoute
```


License
-------

Pathfinder is released under the Apache 2.0 license.
