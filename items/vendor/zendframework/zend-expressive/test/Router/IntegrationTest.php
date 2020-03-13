<?php
/**
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Router;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\AuraRouter;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Expressive\Router\RouteCollector;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\MiddlewarePipe;
use ZendTest\Expressive\ContainerTrait;

use function array_pop;
use function sprintf;

class IntegrationTest extends TestCase
{
    use ContainerTrait;

    /** @var Response */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var RouterInterface|ObjectProphecy */
    private $router;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    public function setUp()
    {
        $this->response  = new Response();
        $this->responseFactory = function () {
            return $this->response;
        };
        $this->router    = $this->prophesize(RouterInterface::class);
        $this->container = $this->mockContainerInterface();
    }

    public function getApplication()
    {
        return $this->createApplicationFromRouter($this->router->reveal());
    }

    public function createApplicationFromRouter(RouterInterface $router)
    {
        $container = new MiddlewareContainer($this->container->reveal());
        $factory = new MiddlewareFactory($container);
        $pipeline = new MiddlewarePipe();
        $collector = new RouteCollector($router);
        $runner = $this->prophesize(RequestHandlerRunner::class)->reveal();
        return new Application(
            $factory,
            $pipeline,
            $collector,
            $runner
        );
    }

    /**
     * Get the router adapters to test
     */
    public function routerAdapters()
    {
        return [
            'aura'       => [AuraRouter::class],
            'fast-route' => [FastRouteRouter::class],
            'zf2'        => [ZendRouter::class],
        ];
    }

    /**
     * Create an Application object with 2 routes, a GET and a POST
     * using Application::get() and Application::post()
     *
     * @param string $adapter
     * @param string $getName
     * @param string $postName
     * @return Application
     */
    private function createApplicationWithGetPost($adapter, $getName = null, $postName = null)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));
        $app->pipe(new MethodNotAllowedMiddleware($this->responseFactory));

        $app->get('/foo', function ($req, $handler) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware GET');
            return $this->response->withBody($stream);
        }, $getName);
        $app->post('/foo', function ($req, $handler) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware POST');
            return $this->response->withBody($stream);
        }, $postName);

        return $app;
    }

    /**
     * Create an Application object with 2 routes, a GET and a POST
     * using Application::route()
     *
     * @param string $adapter
     * @param string $getName
     * @param string $postName
     * @return Application
     */
    private function createApplicationWithRouteGetPost($adapter, $getName = null, $postName = null)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));
        $app->pipe(new MethodNotAllowedMiddleware($this->responseFactory));

        $app->route('/foo', function ($req, $handler) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware GET');
            return $this->response->withBody($stream);
        }, ['GET'], $getName);
        $app->route('/foo', function ($req, $handler) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware POST');
            return $this->response->withBody($stream);
        }, ['POST'], $postName);

        return $app;
    }

    /**
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingDoesNotMatchMethod($adapter)
    {
        $app = $this->createApplicationWithGetPost($adapter);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'DELETE'], [], '/foo', 'DELETE');
        $result  = $app->process($request, $handler->reveal());

        $this->assertSame(StatusCode::STATUS_METHOD_NOT_ALLOWED, $result->getStatusCode());
        $headers = $result->getHeaders();
        $this->assertSame(['GET,POST'], $headers['Allow']);
    }

    /**
     * @see https://github.com/zendframework/zend-expressive/issues/40
     * @group 40
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingWithSamePathWithoutName($adapter)
    {
        $app = $this->createApplicationWithGetPost($adapter);
        $app->pipe(new DispatchMiddleware());

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware GET', (string) $result->getBody());

        $request  = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], '/foo', 'POST');
        $result   = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware POST', (string) $result->getBody());
    }

    /**
     * @see https://github.com/zendframework/zend-expressive/issues/40
     * @group 40
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingWithSamePathWithName($adapter)
    {
        $app = $this->createApplicationWithGetPost($adapter, 'foo-get', 'foo-post');
        $app->pipe(new DispatchMiddleware());

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware GET', (string) $result->getBody());

        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], '/foo', 'POST');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware POST', (string) $result->getBody());
    }

    /**
     * @see https://github.com/zendframework/zend-expressive/issues/40
     * @group 40
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingWithSamePathWithRouteWithoutName($adapter)
    {
        $app = $this->createApplicationWithRouteGetPost($adapter);
        $app->pipe(new DispatchMiddleware());

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware GET', (string) $result->getBody());

        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], '/foo', 'POST');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware POST', (string) $result->getBody());
    }

    /**
     * @see https://github.com/zendframework/zend-expressive/issues/40
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingWithSamePathWithRouteWithName($adapter)
    {
        $app = $this->createApplicationWithRouteGetPost($adapter, 'foo-get', 'foo-post');
        $app->pipe(new DispatchMiddleware());

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware GET', (string) $result->getBody());

        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], '/foo', 'POST');
        $result  = $app->process($request, $handler->reveal());

        $this->assertEquals('Middleware POST', (string) $result->getBody());
    }

    /**
     * @see https://github.com/zendframework/zend-expressive/issues/40
     * @group 40
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testRoutingWithSamePathWithRouteWithMultipleMethods($adapter)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));
        $app->pipe(new MethodNotAllowedMiddleware($this->responseFactory));
        $app->pipe(new DispatchMiddleware());

        $response = clone $this->response;
        $app->route('/foo', function ($req, $handler) use ($response) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware GET, POST');
            return $response->withBody($stream);
        }, ['GET', 'POST']);

        $deleteResponse = clone $this->response;
        $app->route('/foo', function ($req, $handler) use ($deleteResponse) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware DELETE');
            return $deleteResponse->withBody($stream);
        }, ['DELETE']);

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());
        $this->assertEquals('Middleware GET, POST', (string) $result->getBody());

        $request = new ServerRequest(['REQUEST_METHOD' => 'POST'], [], '/foo', 'POST');
        $result  = $app->process($request, $handler->reveal());
        $this->assertEquals('Middleware GET, POST', (string) $result->getBody());

        $request = new ServerRequest(['REQUEST_METHOD' => 'DELETE'], [], '/foo', 'DELETE');
        $result  = $app->process($request, $handler->reveal());
        $this->assertEquals('Middleware DELETE', (string) $result->getBody());
    }

    public function routerAdaptersForHttpMethods()
    {
        $allMethods = [
            RequestMethod::METHOD_GET,
            RequestMethod::METHOD_POST,
            RequestMethod::METHOD_PUT,
            RequestMethod::METHOD_DELETE,
            RequestMethod::METHOD_PATCH,
            RequestMethod::METHOD_HEAD,
            RequestMethod::METHOD_OPTIONS,
        ];
        foreach ($this->routerAdapters() as $adapterName => $adapter) {
            $adapter = array_pop($adapter);
            foreach ($allMethods as $method) {
                $name = sprintf('%s-%s', $adapterName, $method);
                yield $name => [$adapter, $method];
            }
        }
    }

    /**
     * @dataProvider routerAdaptersForHttpMethods
     *
     * @param string $adapter
     * @param string $method
     */
    public function testMatchWithAllHttpMethods($adapter, $method)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));
        $app->pipe(new MethodNotAllowedMiddleware($this->responseFactory));
        $app->pipe(new DispatchMiddleware());

        // Add a route with Zend\Expressive\Router\Route::HTTP_METHOD_ANY
        $response = clone $this->response;
        $app->route('/foo', function ($req, $handler) use ($response) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware');
            return $response->withBody($stream);
        });

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => $method], [], '/foo', $method);
        $result  = $app->process($request, $handler->reveal());
        $this->assertEquals('Middleware', (string) $result->getBody());
    }

    public function allowedMethod()
    {
        return [
            'aura-head'          => [AuraRouter::class, RequestMethod::METHOD_HEAD],
            'aura-options'       => [AuraRouter::class, RequestMethod::METHOD_OPTIONS],
            'fast-route-head'    => [FastRouteRouter::class, RequestMethod::METHOD_HEAD],
            'fast-route-options' => [FastRouteRouter::class, RequestMethod::METHOD_OPTIONS],
            'zf2-head'           => [ZendRouter::class, RequestMethod::METHOD_HEAD],
            'zf2-options'        => [ZendRouter::class, RequestMethod::METHOD_OPTIONS],
        ];
    }

    /**
     * @dataProvider allowedMethod
     *
     * @param string $adapter
     * @param string $method
     */
    public function testAllowedMethodsWhenOnlyPutMethodSet($adapter, $method)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));
        $app->pipe(new ImplicitHeadMiddleware($router, function () {
        }));
        $app->pipe(new ImplicitOptionsMiddleware($this->responseFactory));
        $app->pipe(new MethodNotAllowedMiddleware($this->responseFactory));
        $app->pipe(new DispatchMiddleware());

        // Add a PUT route
        $app->put('/foo', function ($req, $res, $next) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware');
            return $res->withBody($stream);
        });

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequest::class))
            ->shouldNotBeCalled();

        $request = new ServerRequest(['REQUEST_METHOD' => $method], [], '/foo', $method);
        $result  = $app->process($request, $handler->reveal());

        if ($method === RequestMethod::METHOD_OPTIONS) {
            $this->assertSame(StatusCode::STATUS_OK, $result->getStatusCode());
        } else {
            $this->assertSame(StatusCode::STATUS_METHOD_NOT_ALLOWED, $result->getStatusCode());
        }
        $this->assertSame('', (string) $result->getBody());
    }

    /**
     * @group 74
     *
     * @dataProvider routerAdapters
     *
     * @param string $adapter
     */
    public function testWithOnlyRootPathRouteDefinedRoutingToSubPathsShouldDelegate($adapter)
    {
        $router = new $adapter();
        $app = $this->createApplicationFromRouter($router);
        $app->pipe(new RouteMiddleware($router));

        $response = clone $this->response;
        $app->route('/', function ($req, $handler) use ($response) {
            $stream = new Stream('php://temp', 'w+');
            $stream->write('Middleware');
            return $response->withBody($stream);
        }, ['GET']);

        $expected = $this->response->withStatus(StatusCode::STATUS_NOT_FOUND);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->willReturn($expected);

        $request = new ServerRequest(['REQUEST_METHOD' => 'GET'], [], '/foo', 'GET');
        $result  = $app->process($request, $handler->reveal());
        $this->assertSame($expected, $result);
    }
}
