<?php
/**
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;
use Zend\Expressive\Container\Exception\InvalidServiceException;
use Zend\Expressive\Container\StreamFactoryFactory;

use function class_exists;
use function get_class;
use function is_array;
use function is_object;
use function preg_match;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;

class StreamFactoryFactoryWithoutDiactorosTest extends TestCase
{
    private $autoloadFunctions = [];

    public function setUp()
    {
        class_exists(InvalidServiceException::class);

        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
        $this->factory = new StreamFactoryFactory();

        foreach (spl_autoload_functions() as $autoloader) {
            if (! is_array($autoloader)) {
                continue;
            }

            $context = $autoloader[0];

            if (! is_object($context)
                || ! preg_match('/^Composer.*?ClassLoader$/', get_class($context))
            ) {
                continue;
            }

            $this->autoloadFunctions[] = $autoloader;

            spl_autoload_unregister($autoloader);
        }
    }

    public function tearDown()
    {
        $this->reloadAutoloaders();
    }

    public function reloadAutoloaders()
    {
        foreach ($this->autoloadFunctions as $autoloader) {
            spl_autoload_register($autoloader);
        }
        $this->autoloadFunctions = [];
    }

    public function testFactoryRaisesAnExceptionIfDiactorosIsNotLoaded()
    {
        $e = null;

        try {
            ($this->factory)($this->container);
        } catch (Throwable $e) {
        }

        $this->reloadAutoloaders();

        $this->assertInstanceOf(InvalidServiceException::class, $e);
        $this->assertContains('zendframework/zend-diactoros', $e->getMessage());
    }
}
