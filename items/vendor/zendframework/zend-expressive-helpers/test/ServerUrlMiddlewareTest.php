<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Helper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\ServerUrlMiddleware;

class ServerUrlMiddlewareTest extends TestCase
{
    public function testMiddlewareInjectsHelperWithUri()
    {
        $uri = $this->prophesize(UriInterface::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());

        $helper = new ServerUrlHelper();
        $middleware = new ServerUrlMiddleware($helper);

        $invoked = false;

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(RequestInterface::class))->will(function ($req) use (&$invoked) {
            $invoked = true;

            return new Response();
        });

        $test = $middleware->process($request->reveal(), $handler->reveal());
        //$this->assertSame($response->reveal(), $test, 'Unexpected return value from middleware');
        $this->assertTrue($invoked, 'next() was not invoked');

        $this->assertAttributeSame(
            $uri->reveal(),
            'uri',
            $helper,
            'Helper was not injected with URI from request'
        );
    }
}
