<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-eventmanager for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-eventmanager/blob/master/LICENSE.md
 */

namespace ZendTest\EventManager;

use PHPUnit\Framework\TestCase;
use Zend\EventManager\Filter\FilterIterator;
use Zend\EventManager\FilterChain;

/**
 * @group      Zend_Stdlib
 */
class FilterChainTest extends TestCase
{
    /**
     * @var FilterChain
     */
    protected $filterchain;

    public function setUp()
    {
        if (isset($this->message)) {
            unset($this->message);
        }
        $this->filterchain = new FilterChain;
    }

    public function testSubscribeShouldReturnCallbackHandler()
    {
        $handle = $this->filterchain->attach([ $this, __METHOD__ ]);
        $this->assertSame([ $this, __METHOD__ ], $handle);
    }

    public function testSubscribeShouldAddCallbackHandlerToFilters()
    {
        $handler  = $this->filterchain->attach([$this, __METHOD__]);
        $handlers = $this->filterchain->getFilters();
        $this->assertEquals(1, count($handlers));
        $this->assertTrue($handlers->contains($handler));
    }

    public function testDetachShouldRemoveCallbackHandlerFromFilters()
    {
        $handle = $this->filterchain->attach([ $this, __METHOD__ ]);
        $handles = $this->filterchain->getFilters();
        $this->assertTrue($handles->contains($handle));
        $this->filterchain->detach($handle);
        $handles = $this->filterchain->getFilters();
        $this->assertFalse($handles->contains($handle));
    }

    public function testDetachShouldReturnFalseIfCallbackHandlerDoesNotExist()
    {
        $handle1 = $this->filterchain->attach([ $this, __METHOD__ ]);
        $this->filterchain->clearFilters();
        $handle2 = $this->filterchain->attach([ $this, 'handleTestTopic' ]);
        $this->assertFalse($this->filterchain->detach($handle1));
    }

    public function testRetrievingAttachedFiltersShouldReturnEmptyArrayWhenNoFiltersExist()
    {
        $handles = $this->filterchain->getFilters();
        $this->assertEquals(0, count($handles));
    }

    public function testFilterChainShouldReturnLastResponse()
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            $return = $chain->next($context, $params, $chain);
            return $return;
        });
        $this->filterchain->attach(function ($context, array $params) {
            $string = isset($params['string']) ? $params['string'] : '';
            return str_rot13($string);
        });
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        $this->assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function testFilterIsPassedContextAndArguments()
    {
        $this->filterchain->attach([ $this, 'filterTestCallback1' ]);
        $obj = (object) ['foo' => 'bar', 'bar' => 'baz'];
        $value = $this->filterchain->run($this, ['object' => $obj]);
        $this->assertEquals('filtered', $value);
        $this->assertEquals('filterTestCallback1', $this->message);
        $this->assertEquals('foobarbaz', $obj->foo);
    }

    public function testInterceptingFilterShouldReceiveChain()
    {
        $this->filterchain->attach([$this, 'filterReceivalCallback']);
        $this->filterchain->run($this);
    }

    public function testFilteringStopsAsSoonAsAFilterFailsToCallNext()
    {
        $this->filterchain->attach(function ($context, $params, $chain) {
            if (isset($params['string'])) {
                $params['string'] = trim($params['string']);
            }
            return $chain->next($context, $params, $chain);
        }, 10000);
        $this->filterchain->attach(function ($context, array $params) {
            $string = isset($params['string']) ? $params['string'] : '';
            return str_rot13($string);
        }, 1000);
        $this->filterchain->attach(function ($context, $params, $chain) {
            $string = isset($params['string']) ? $params['string'] : '';
            return hash('md5', $string);
        }, 100);
        $value = $this->filterchain->run($this, ['string' => ' foo ']);
        $this->assertEquals(str_rot13(trim(' foo ')), $value);
    }

    public function handleTestTopic($message)
    {
        $this->message = $message;
    }

    public function filterTestCallback1($context, array $params)
    {
        $context->message = __FUNCTION__;
        if (isset($params['object']) && is_object($params['object'])) {
            $params['object']->foo = 'foobarbaz';
        }
        return 'filtered';
    }

    public function filterReceivalCallback($context, array $params, $chain)
    {
        $this->assertInstanceOf(FilterIterator::class, $chain);
    }

    public function testRunReturnsNullWhenChainIsEmpty()
    {
        $filterChain = new FilterChain();
        $this->assertNull($filterChain->run(null));
    }

    public function testGetResponses()
    {
        $filterChain = new FilterChain();
        $this->assertNull($filterChain->getResponses());
    }
}
