<?php
/**
 * @see       https://github.com/zendframework/zend-httphandlerrunner for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-httphandlerrunner/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\HttpHandlerRunner;

use PHPUnit\Framework\TestCase;
use Zend\HttpHandlerRunner\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
    }
}
