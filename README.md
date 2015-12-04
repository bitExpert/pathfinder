# bitexpert/pathfinder
A PHP routing component.

## How to use ...

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

## How to implement your own ...

Matchers
--------
Each matcher in Pathfinder must implement the \bitExpert\Pathfinder\Matcher\Matcher interface.

```php
class OnlyDigitMatcher implements \bitExpert\Pathfinder\Matcher\Matcher {
    public function match($value) {
        return ctype_digit($value);
    }
}
```

Routes
------
Routes in Pathfinder must be an instance of the \bitExpert\Pathfinder\Route class.

```php
$route = \bitExpert\Pathfinder\Route::get('/user/[:id]')->to('users')->ifMatches('id', new OnlyDigitMatcher());
```

Router
------
Each router in Pathfinder must implement the \bitExpert\Pathfinder\Router interface.

```php
use \Psr\Http\Message\ServerRequestInterface;
use \bitExpert\Pathfinder\Route;

class SimpleRouter implements \bitExpert\Pathfinder\Router {

 protected $routes = [];
 protected $defaultTarget;

 public function addRoute(Route $route) {
    $this->routes[] = $route;
    return $this;
 }
 
 public function setRoutes(array $routes) {
    foreach($routes as $route) {
        $this->addRoute($route);
    }
    return $this;
 }
 
 public function getTargetRequestAttribute() {
    return self::DEFAULT_TARGET_REQUEST_ATTRIBUTE;
 }
 
 public function setDefaultTarget($defaultTarget) {
    $this->defaultTarget = $defaultTarget;
    return $this;
 }
 
 public function match(ServerRequestInterface $request) {
    // using \bitExpert\Pathfinder\Psr7Router::match() logic ...
 }
 
 public function generateUri($routeIdentifier, array $params = []) {
     // using \bitExpert\Pathfinder\Psr7Router::generateUri() logic ...
 }
}
```





License
-------

Pathfinder is released under the Apache 2.0 license.