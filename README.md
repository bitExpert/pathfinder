# bitexpert/pathfinder
A PHP routing component.

[![Build Status](https://travis-ci.org/bitExpert/pathfinder.svg?branch=master)](https://travis-ci.org/bitExpert/pathfinder)
[![Dependency Status](https://www.versioneye.com/php/bitexpert:pathfinder/dev-master/badge?style=flat)](https://www.versioneye.com/php/bitexpert:pathfinder/dev-master)

Router
------

The router is responsible for resolving the target from the given route as well building an uri for a given 
route identifier (and it`s parameters). Using the Psr7Router (\bitExpert\Pathfinder\Psr7Router) is pretty easy:

```php
$baseUrl = 'http://myapp.loc:8080';
$router = new \bitExpert\Pathfinder\Psr7Router($baseUrl);
$router->setRoutes(
    [
        new Route('GET', '/', 'index'),
        new Route('GET', '/question/[:title]_[:id]', 'question'),
        new Route(['GET', 'POST'], '/editquestion', 'editquestion')
    ]
);
```

Routes
------
Routes in Pathfinder are implemented immutual. You may define your routes in several styles:

```php
new Route('GET', '/', 'index');
Route::create('GET', '/', 'index');
Route::get('/')->to('index');
Route::create()->from('/')->to('index')->accepting('GET');
Route::get('/pathtofunctarget')->to(function () {
    // callable target contents
})->named('routewithcallabletarget');
```

You may also mix all the styles above, just as you like, since every method returns a new instance of route.

Matchers
--------
Matchers are used to ensure that your route params match given criteria such as digits only:

```php
Route::get('/user/[:id]')->to('users')->ifMatches('id', new NumericMatcher());
```

Middleware
----------
You may use the routing middleware to integrate Pathfinder into your PSR-7 compatible project:

```php
use bitExpert\Pathfinder\Route;
use bitExpert\Pathfinder\Psr7Router;
use bitExpert\Pathfinder\Middleware\BasicRoutingMiddleware;

$router = new Psr7Router();
$router->addRoute(Route::get('/')->to('home'));

// The routing middleware will use the given router to match the request and will set the routing result as value
// of the request attribute named 'routingResult' for further use
$routingMiddleware = new BasicRoutingMiddleware($router, 'routingResult');
```
 
Errors
------
The (bitExpert\Pathfinder\Psr7Router) does not throw an Exception if the request doesn't match any route.
It will still return a (bitExpert\Pathfinder\RoutingResult) returning true when calling $routingResult->failed();
You may receive the failure reason by calling $routingResult->getFailure() which will give you information about
the failure reason and an optional route which could have matched, but didn't fulfill all criteria:

```php
use bitExpert\Pathfinder\Psr7Router;
use Zend\Diactoros\ServerRequest;
use bitExpert\Pathfinder\Matcher\NumericMatcher;

$router = new Psr7Router();

$homeRoute = Route::get('/')->to('home');
$orderUpdateRoute = Route::put('/order/[:orderId]')->to('updateOrder')->ifMatches('id', new NumericMatcher());
 
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
