# bitexpert/pathfinder
A PHP routing component.

[![Build Status](https://travis-ci.org/bitExpert/pathfinder.svg?branch=v0.3.0)](https://travis-ci.org/bitExpert/pathfinder)
[![Dependency Status](https://www.versioneye.com/user/projects/5736e522a0ca35004baf924e/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5736e522a0ca35004baf924e)

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

License
-------

Pathfinder is released under the Apache 2.0 license.
