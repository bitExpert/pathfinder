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
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();

        $matcherMockBuilder = $this->getMockBuilder(Matcher::class)->setMethods(['__invoke']);

        $notMatchingMatcher = $matcherMockBuilder->getMock();
        $notMatchingMatcher->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValue(false));

        $matchingMatcher = $matcherMockBuilder->getMock();
        $matchingMatcher->expects($this->any())
            ->method('__invoke')
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
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertNull($result->getTarget());
    }

    /**
     * @test
     */
    public function noMatchingMethodWillReturnDefaultTarget()
    {
        $this->request = new ServerRequest([], [], '/users', 'HEAD');

        $this->router->setDefaultTarget('default.target');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertSame('default.target', $result->getTarget());
    }

    /**
     * @test
     */
    public function noMatchingRouteWillReturnDefaultTarget()
    {
        $this->router->setDefaultTarget('default.target');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertSame('default.target', $result->getTarget());
    }

    /**
     * @test
     */
    public function noMatchingRouteWillReturnNullWhenNoDefaultTargetWasSet()
    {
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertNull($result->getTarget());
    }

    /**
     * @test
     */
    public function matchingRouteWithoutParamsReturnsTarget()
    {
        $this->request = new ServerRequest([], [], '/users', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->succeeded());
        $this->assertSame('my.GetActionToken', $result->getTarget());
    }

    /**
     * @test
     */
    public function matchingRouteWithParamsReturnsTargetAndParams()
    {
        $this->request = new ServerRequest([], [], '/user/123', 'GET');
        $result = $this->router->match($this->request);

        $params = $result->getParams();

        $this->assertTrue($result->succeeded());
        $this->assertSame('my.GetActionTokenWithParam', $result->getTarget());
        $this->assertTrue(isset($params['userId']));
        $this->assertSame('123', $params['userId']);
    }

    /**
     * @test
     */
    public function doesNotUseRouteIfMatcherDoesNotMatch()
    {
        $this->request = new ServerRequest([], [], '/company/abc', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->failed());
        $this->assertNull($result->getTarget());
    }

    /**
     * @test
     */
    public function usesRouteIfMatcherDoesMatch()
    {
        $this->request = new ServerRequest([], [], '/offer/123', 'GET');
        $result = $this->router->match($this->request);

        $this->assertTrue($result->succeeded());
        $this->assertEquals('my.GetActionTokenWithMatchedParam', $result->getTarget());
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
     * @expectedException \ConfigurationException
     */
    public function throwsAnExceptionIfAddedRouteHasNoMethodDefined()
    {
        $this->router->addRoute(Route::create()->to('someaction'));
    }

    /**
     * @test
     * @expectedException \ConfigurationException
     */
    public function throwsAnExceptionIfPathOfAddedRouteIsMissing()
    {
        $this->router->addRoute(Route::get()->to('someaction'));
    }

    /**
     * @test
     * @expectedException \ConfigurationException
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
        $this->router->addRoute(Route::get('/something')->to('someaction'));
        $this->request = new ServerRequest([], [], '/something', 'GET');

        $result = $this->router->match($this->request);
        $this->assertTrue($result->succeeded());
        $this->assertEquals('someaction', $result->getTarget());
    }

    /**
     * @test
     * @expectedException \ConfigurationException
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
