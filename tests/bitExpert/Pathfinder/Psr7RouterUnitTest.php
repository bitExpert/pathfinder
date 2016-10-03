<?php

/**
 * This file is part of the Pathfinder package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Pathfinder;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use bitExpert\Pathfinder\Matcher\Matcher;

/**
 * Unit test for {@link \bitExpert\Pathfinder\Psr7Router}.
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
        $this->router = new Psr7Router();
    }

    /**
     * @test
     */
    public function noMatchingMethodWillReturnMethodNotAllowedFailureAndFirstCandidate()
    {
        $this->request = new ServerRequest([], [], '/users', 'HEAD');
        $route = RouteBuilder::route()->get('/users')->to('my.GetActionToken')->build();
        $this->router->setRoutes(
            [
                $route,
                RouteBuilder::route()->post('/users')->to('my.PostActionToken')->build()
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
        $route = RouteBuilder::route()->get('/users')->to('userListAction')->build();
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
        $route = RouteBuilder::route()->get('/user/[:userId]')->to('userDetailsAction')->build();

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
        $route = RouteBuilder::route()
            ->get('/company/[:companyId]')
            ->to('my.GetActionTokenWithUnmatchedParam')
            ->ifMatches('companyId', $this->notMatchingMatcher)
            ->build();

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
        $route = RouteBuilder::route()
            ->get('/offer/[:offerId]')
            ->to('my.GetActionTokenWithMatchedParam')
            ->ifMatches('offerId', $this->matchingMatcher)
            ->build();

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
     */
    public function returnsAFalsyRoutingResultContainingBadRequestReason()
    {
        $paramValue = 'value';
        $matcher = $this->getMockForAbstractClass(Matcher::class);
        $matcher->expects($this->once())
            ->method('__invoke')
            ->with($paramValue)
            ->will($this->returnValue(false));

        $this->router->addRoute(
            RouteBuilder::route()
                ->get('/[:param]')
                ->to('action')
                ->ifMatches('param', $matcher)
                ->build()
        );

        $this->request = new ServerRequest([], [], '/' . $paramValue, 'GET');
        $result = $this->router->match($this->request);
        $this->assertTrue($result->failed());
        $this->assertEquals(RoutingResult::FAILED_BAD_REQUEST, $result->getFailure());
    }

    /**
     * @test
     */
    public function addsRouteCorrectlyIfValid()
    {
        $route = RouteBuilder::route()->get('/something')->to('someaction')->build();
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
        $this->router->addRoute(RouteBuilder::route()->get('/something')->to(function () {
            // do nothing
        })->build());
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
        $route = RouteBuilder::route()->get($routeUrl)->to('my.GetActionToken')->build();
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionToken');

        $this->assertSame($routeUrl, $url);
    }

    /**
     * @test
     * @dataProvider paramsProvider
     */
    public function paramsAreIgnoredForRoutesWithoutAnyParams(string $paramname, $paramvalue)
    {
        $route = RouteBuilder::route()->get('/users')->to('my.GetActionToken')->build();
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionToken', [$paramname => $paramvalue]);

        $this->assertSame('/users', $url);
    }

    /**
     * @test
     */
    public function routeParamPlaceholdersWillBeReplaced()
    {
        $route = RouteBuilder::route()->get('/user/[:userId]')->to('my.GetActionTokenWithParam')->build();
        $this->router->addRoute($route);
        $url = $this->router->generateUri('my.GetActionTokenWithParam', ['userId' => 123]);

        $this->assertSame('/user/123', $url);
    }

    /**
     * @test
     */
    public function paramsNotFoundInRouteWillBeIgnoredWhenLinkIsAssembled()
    {
        $route = RouteBuilder::route()->get('/user/[:userId]')->to('my.GetActionTokenWithParam')->build();

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
        $this->router->addRoute(
            RouteBuilder::route()->get('/[:sampleId]/[:missingParam]')->to('my.GetActionTokenWithParam')->build()
        );
        $this->router->generateUri('my.GetActionTokenWithParam', ['sampleId' => 123]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowAnExceptionWhenProvidingNotMatchingParams()
    {
        $paramValue = 'abc';
        $matcher = $this->createMock(Matcher::class);
        $matcher->expects($this->once())
            ->method('__invoke')
            ->with($paramValue)
            ->will($this->returnValue(false));

        $this->router->addRoute(
            RouteBuilder::route()
                ->get('/company/[:companyId]')
                ->to('companyAction')
                ->ifMatches('companyId', $matcher)
                ->build()
        );
        $this->router->generateUri('companyAction', ['companyId' => $paramValue]);
    }

    public function paramsProvider()
    {
        return [
            ['sampleId', ''],
            ['sampleId', '456'],
            ['sampleId', 456],
            ['sampleId', 456.5],
            ['sampleId', false],
            ['sampleId', null],
        ];
    }
}
