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
 * Unit test for {@link \bitExpert\Pathfinder\PathRouter}.
 *
 * @covers \bitExpert\Pathfinder\PathRouter
 */
class Psr7RouterUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PathRouter
     */
    protected $router;
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $matcherMockBuilder = $this->getMockBuilder(Matcher::class)->setMethods(['match']);

        $notMatchingMatcher = $matcherMockBuilder->getMock();
        $notMatchingMatcher->expects($this->any())
            ->method('match')
            ->will($this->returnValue(false));

        $matchingMatcher = $matcherMockBuilder->getMock();
        $matchingMatcher->expects($this->any())
            ->method('match')
            ->will($this->returnValue(true));

        $this->request = new ServerRequest();
        $this->router = new Psr7Router('http://localhost');
        $this->router->setRoutes(
            [
                Route::get('/users')->to('my.GetActionToken'),
                Route::post('/users')->to('my.PostActionToken'),
                Route::get('/user/[:userId]')->to('my.GetActionTokenWithParam'),
                Route::get('/companies')->to('my.OtherGetActionToken'),
                Route::get('/offer/[:offerId]')
                    ->to('my.GetActionTokenWithMatchedParam')
                    ->ifMatches('offerId', $matchingMatcher),
                Route::get('/company/[:companyId]')
                    ->to('my.GetActionTokenWithUnmatchedParam')
                    ->ifMatches('companyId', $notMatchingMatcher),
            ]
        );
    }

    /**
     * @test
     */
    public function noMatchingMethodWillReturnNullWhenNoDefaultTargetWasSet()
    {
        $this->request = new ServerRequest([], [], '/users', 'HEAD');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();

        $this->assertNull($this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function noMatchingMethodWillReturnDefaultTarget()
    {
        $this->request = new ServerRequest([], [], '/users', 'HEAD');

        $this->router->setDefaultTarget('default.target');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();

        $this->assertSame('default.target', $this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function noMatchingRouteWillReturnDefaultTarget()
    {
        $this->router->setDefaultTarget('default.target');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();

        $this->assertSame('default.target', $this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function noMatchingRouteWillReturnNullWhenNoDefaultTargetWasSet()
    {
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertNull($this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function queryStringWillBeIgnoredWhenMatchingRoute()
    {
        $this->request = new ServerRequest([], [], '/users?sessid=ABDC', 'GET');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertSame('my.GetActionToken', $this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function matchingRouteWithoutParamsReturnsTarget()
    {
        $this->request = new ServerRequest([], [], '/users', 'GET');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertSame('my.GetActionToken', $this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function matchingRouteWithParamsReturnsTargetAndSetsParamsInRequest()
    {
        $this->request = new ServerRequest([], [], '/user/123', 'GET');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();

        $queryParams = $this->request->getQueryParams();

        $this->assertSame('my.GetActionTokenWithParam', $this->request->getAttribute($targetRequestAttribute));
        $this->assertTrue(isset($queryParams['userId']));
        $this->assertSame('123', $queryParams['userId']);
    }

    /**
     * @test
     */
    public function doesNotUseRouteIfMatcherDoesNotMatch()
    {
        $this->request = new ServerRequest([], [], '/company/abc', 'GET');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertNull($this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function usesRouteIfMatcherDoesMatch()
    {
        $this->request = new ServerRequest([], [], '/offer/123', 'GET');
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertEquals('my.GetActionTokenWithMatchedParam', $this->request->getAttribute($targetRequestAttribute));
    }

    /**
     * @test
     */
    public function passingRegexRouterAsConfig()
    {
        $router = new Psr7Router('http://localhost');
        $router->setRoutes([new Route('GET', '/admin', 'my.AdminActionToken')]);

        $this->request = new ServerRequest([], [], '/admin', 'GET');
        $this->router->setRoutes([$router]);
        $this->request = $this->router->match($this->request);
        $targetRequestAttribute = $this->router->getTargetRequestAttribute();
        $this->assertSame('my.AdminActionToken', $this->request->getAttribute($targetRequestAttribute));
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
    public function throwsAnExceptionWhenRouteToTargetCouldNotBeFound()
    {
        $this->router->generateUri('nonexistent.actionToken');
    }

    /**
     * @test
     */
    public function returnsTargetWhenMatchingRouteIsFound()
    {
        $url = $this->router->generateUri('my.GetActionToken');

        $this->assertSame('/users', $url);
    }

    /**
     * @test
     */
    public function paramsAreIgnoredForRoutesWithoutAnyParams()
    {
        $url = $this->router->generateUri('my.GetActionToken', ['sampleId' => 456]);

        $this->assertSame('/users', $url);
    }

    /**
     * @test
     */
    public function routeParamPlaceholdersWillBeReplaced()
    {
        $url = $this->router->generateUri('my.GetActionTokenWithParam', ['userId' => 123]);

        $this->assertSame('/user/123', $url);
    }

    /**
     * @test
     */
    public function paramsNotFoundInRouteWillBeIgnoredWhenLinkIsAssembled()
    {
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
