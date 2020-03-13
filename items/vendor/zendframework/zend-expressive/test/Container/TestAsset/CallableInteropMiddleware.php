<?php
/**
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Container\TestAsset;

use Psr\Http\Server\RequestHandlerInterface;

class CallableInteropMiddleware
{
    public function __invoke($request, RequestHandlerInterface $handler)
    {
    }
}
