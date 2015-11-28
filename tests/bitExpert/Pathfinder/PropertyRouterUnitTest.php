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

/**
 * Unit test for {@link \bitExpert\Pathfinder\PropertyRouter}.
 *
 * @covers \bitExpert\Pathfinder\PropertyRouter
 */
class PropertyRouterUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyRouter
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

        $this->request = new ServerRequest();
        $this->router = new PropertyRouter('http://localhost');
    }

    /**
     * @test
     */
    public function defaultRouterSetupWillReadActionParameter()
    {
        $this->request = $this->request->withQueryParams(['action' => 'sample.action']);

        $this->request = $this->router->resolveTarget($this->request);

        $this->assertSame('sample.action', $this->request->getAttribute($this->router->getTargetRequestAttribute()));
    }

    /**
     * @test
     */
    public function changingListenerVariableWillBeRespectedWhenDeterminingTarget()
    {
        $this->request = $this->request->withQueryParams(['mylistener' => 'sample.action']);

        $this->router->setListener('mylistener');
        $this->request = $this->router->resolveTarget($this->request);

        $this->assertSame('sample.action', $this->request->getAttribute($this->router->getTargetRequestAttribute()));
    }

    /**
     * @test
     */
    public function missingListenerInRequestWillReturnDefaultTarget()
    {
        $this->router->setDefaultTarget('default.actionToken');
        $this->request = $this->router->resolveTarget($this->request);

        $this->assertSame('default.actionToken', $this->request->getAttribute($this->router->getTargetRequestAttribute()));
    }

    /**
     * @test
     */
    public function missingListenerAndMissingDefaultTargetWillReturnNull()
    {
        $this->request = $this->router->resolveTarget($this->request);

        $this->assertNull($this->request->getAttribute($this->router->getTargetRequestAttribute()));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function callingCreateLinkWithoutTargetWillThrowException()
    {
        $this->router->createLink('');
    }

    /**
     * @test
     */
    public function createLinkWillConvertParamsIntoUrlWithoutSpecialChars()
    {
        $link = $this->router->createLink('myAction', ['param1' => 'value1', 'param2' => 'value2']);

        $url = parse_url($link);
        $this->assertSame('http', $url['scheme']);
        $this->assertSame('localhost', $url['host']);
        $this->assertSame('action=myAction&param1=value1&param2=value2', $url['query']);
    }

    /**
     * @test
     */
    public function createLinkWillConvertParamsIntoUrlWithSpecialChars()
    {
        $this->router->setSpecialCharEncoding(true);
        $link = $this->router->createLink('myAction', ['param1' => 'value1', 'param2' => 'value2']);

        $url = parse_url($link);
        $this->assertSame('http', $url['scheme']);
        $this->assertSame('localhost', $url['host']);
        $this->assertSame('action=myAction&amp;param1=value1&amp;param2=value2', $url['query']);
    }
}
