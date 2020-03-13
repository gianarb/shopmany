<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-router for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Router\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\DispatchMiddlewareFactory;

class DispatchMiddlewareFactoryTest extends TestCase
{
    public function testFactoryProducesDispatchMiddleware()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new DispatchMiddlewareFactory();
        $middleware = $factory($container);
        $this->assertInstanceOf(DispatchMiddleware::class, $middleware);
    }
}
