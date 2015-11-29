# pathfinder
A PHP routing component


Router
------

The router is responsible for resolving the actionToken from the given route as well building an url for a given 
actionToken (and it`s parameters). The router comes in two flavours, once as a simple PropertyRouter 
(\bitExpert\Adroit\Router\PropertyRouter) which will look up the actionToken based on an url parameter or the RegexRouter
(\bitExpert\Adroit\Router\RegexRouter) which will map the whole url to an actionToken.

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
Routes in Adroit are implemented immutual. You may define your routes in several styles:

```php
new Route('GET', '/', 'index');
Route::create('GET', '/', 'index');
Route::get('/')->to('index');
Route::create()->from('/')->to('index')->accepting('GET');
Route::get('/pathtofunctarget')->to(function () {
    // callable target contents
})->named('mycallableroute');
```

You may also mix all the styles above, just as you like, since every method returns a new instance of route.

Matchers
--------
Matchers are used to ensure that your route params match given criteria such as digits only:

```php
Route::get('/user/[:id]')->to('users')->ifMatches('id', new NumericMatcher());
```

