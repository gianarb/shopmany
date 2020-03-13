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
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use ZendTest\EventManager\TestAsset\MockEventManagerAwareTrait;

class EventManagerAwareTraitTest extends TestCase
{
    public function testSetEventManager()
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        $this->assertAttributeEquals(null, 'events', $object);

        $eventManager = new EventManager;

        $object->setEventManager($eventManager);

        $this->assertAttributeEquals($eventManager, 'events', $object);
    }

    public function testGetEventManager()
    {
        $object = $this->getObjectForTrait(EventManagerAwareTrait::class);

        $this->assertInstanceOf(EventManagerInterface::class, $object->getEventManager());

        $eventManager = new EventManager;

        $object->setEventManager($eventManager);

        $this->assertSame($eventManager, $object->getEventManager());
    }

    public function testSetEventManagerWithEventIdentifier()
    {
        $object = new MockEventManagerAwareTrait();
        $eventManager = new EventManager();

        $eventIdentifier = 'foo';
        $object->setEventIdentifier($eventIdentifier);

        $object->setEventManager($eventManager);

        //check that the identifier has been added.
        $this->assertContains($eventIdentifier, $eventManager->getIdentifiers());

        //check that the method attachDefaultListeners has been called
        $this->assertTrue($object->defaultEventListenersCalled());
    }
}
