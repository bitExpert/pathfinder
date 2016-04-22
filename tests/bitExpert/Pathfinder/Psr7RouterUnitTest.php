<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Pathfinder;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use bitExpert\Pathfinder\Matcher\Matcher;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Psr7Router}.
 *
 * @covers \bitExpert\Pathfinder\Psr7Router
 */
class Psr7RouterUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Psr7Router
     */
    protected $router;
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var Matcher
     */
    protected $matchingMatcher;
    /**
     * @var Matcher
     */
    protected $notMatchingMatcher;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $matcherMockBuilder = $this->getMockBuilder(Matcher::class)->setMethods(['__invoke']);

        $this->notMatchingMatcher = $matcherMockBuilder->getMock();
        $this->notMatchingMatcher->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValue(false));

        $this->matchingMatcher = $matcherMockBuilder->getMock();
        $this->matchingMatcher->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValue(true));

        $this->request = new ServerRequest();
        $this->router = new Psr7Router('http://localhost');
    }

    /**
     * @test
     */
    public function noMatchingMethodWillReturnMethodNotAllowedFailureAndFirstCandidate()
    {
        $this->request = new ServerRequest([], [], '/users', 'HEAD');
        $route = Route::get('/users')->to('my.GetActionToken');
        $this->router->setRoutes(
            [
                $route,
                Route::post('/users')->to('my.PostActionToken')
            ]
        );
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertEquals(RoutingResult::FAILED_METHOD_NOT_ALLOWED, $result->getFailure());
        $this->assertSame($route, $result->getRoute());
    }

    /**
     * @test
     */
    public function noMatchingRouteWillReturnNotFoundFailure()
    {
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertEquals(null, $result->getRoute());
        $this->assertEquals(RoutingResult::FAILED_NOT_FOUND, $result->getFailure());
    }

    /**
     * @test
     */
    public function matchingRouteWithoutParamsReturnsRoute()
    {
        $route = Route::get('/users')->to('userListAction');
        $this->router->addRoute($route);

        $this->request = new ServerRequest([], [], '/users', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->succeeded());
        $this->assertSame($route, $result->getRoute());
    }

    /**
     * @test
     */
    public function matchingRouteWithParamsReturnsRouteAndParams()
    {
        $route = Route::get('/user/[:userId]')->to('userDetailsAction');

        $this->router->addRoute($route);

        $this->request = new ServerRequest([], [], '/user/123', 'GET');
        $result = $this->router->match($this->request);

        $params = $result->getParams();

        $this->assertTrue($result->succeeded());
        $this->assertSame($route, $result->getRoute());
        $this->assertTrue(isset($params['userId']));
        $this->assertSame('123', $params['userId']);
    }

    /**
     * @test
     */
    public function returnsBadRequestIfMatcherDoesNotMatchAndReturnsCandidate()
    {
        $route = Route::get('/company/[:companyId]')
            ->to('my.GetActionTokenWithUnmatchedParam')
            ->ifMatches('companyId', $this->notMatchingMatcher);

        $this->router->addRoute($route);

        $this->request = new ServerRequest([], [], '/company/abc', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertSame($route, $result->getRoute());
        $this->assertEquals(RoutingResult::FAILED_BAD_REQUEST, $result->getFailure());
    }

    /**
     * @test
     */
    public function usesRouteIfMatcherDoesMatch()
    {
        $route = Route::get('/offer/[:offerId]')
            ->to('my.GetActionTokenWithMatchedParam')
            ->ifMatches('offerId', $this->matchingMatcher);

        $this->router->addRoute($route);
        $this->request = new ServerRequest([], [], '/offer/123', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->succeeded());
        $this->assertSame($route, $result->getRoute());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function callingGenerateUriWithoutTargetWillThrowException()
    {
        $this->router->generateUri('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsAnExceptionIfAddedRouteHasNoMethodDefined()
    {
        $this->router->addRoute(Route::create()->to('someaction'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsAnExceptionIfPathOfAddedRouteIsMissing()
    {
        $this->router->addRoute(Route::get()->to('someaction'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsAnExceptionIfTargetOfAddedRouteIsMissing()
    {
        $this->router->addRoute(Route::get('/something'));
    }

    /**
     * @test
     */
    public function addsRouteCorrectlyIfValid()
    {
        $route = Route::get('/something')->to('someaction');
        $this->router->addRoute($route);
        $this->request = new ServerRequest([], [], '/something', 'GET');

        $result = $this->router->match($this->request);
        $this->assertTrue($result->succeeded());
        $this->assertSame($route, $result->getRoute());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsAnExceptionIfTargetIsCallableAndAddedRouteHasNoNameDefined()
    {
        $this->router->addRoute(Route::get('/something')->to(function () {
            // do nothing
        }));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsAnExceptionWhenRouteToTargetCouldNotBeFound()
    {
        $this->router->generateUri('nonexistent.actionToken');
    }

    /**
     * @test
     */
    public function returnsTargetWhenMatchingRouteIsFound()
    {
        $routeUrl = '/users';
        $route = Route::get($routeUrl)->to('my.GetActionToken');
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionToken');

        $this->assertSame($routeUrl, $url);
    }

    /**
     * @test
     */
    public function paramsAreIgnoredForRoutesWithoutAnyParams()
    {
        $route = Route::get('/users')->to('my.GetActionToken');
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionToken', ['sampleId' => 456]);

        $this->assertSame('/users', $url);
    }

    /**
     * @test
     */
    public function routeParamPlaceholdersWillBeReplaced()
    {
        $route = Route::get('/user/[:userId]')->to('my.GetActionTokenWithParam');
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionTokenWithParam', ['userId' => 123]);

        $this->assertSame('/user/123', $url);
    }

    /**
     * @test
     */
    public function paramsNotFoundInRouteWillBeIgnoredWhenLinkIsAssembled()
    {
        $route = Route::get('/user/[:userId]')->to('my.GetActionTokenWithParam');

        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionTokenWithParam', ['userId' => 123, 'sampleId' => 123]);

        $this->assertSame('/user/123', $url);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowAnExceptionWhenNotAllParamReplacementsAreProvided()
    {
        $this->router->generateUri('my.GetActionTokenWithParam', ['sampleId' => 123]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowAnExceptionWhenProvidingNotMatchingParams()
    {
        $this->router->generateUri('my.GetActionTokenWithUnmatchedParam', ['companyId' => 'abc']);
    }
}
