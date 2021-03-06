<?php
namespace Gamegos\NoSql\Tests\Storage\Event;

/* Imports from PHPUnit */
use PHPUnit_Framework_TestCase;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\StorageInterface;
use Gamegos\NoSql\Storage\OperationArguments;

/* Imports from PHP core */
use Exception;

/**
 * Test Class for OperationEvent
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationEventTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorArguments()
    {
        $key       = 'foo';
        $eventName = 'beforeOperation';
        $storage   = $this->getMockForAbstractClass(StorageInterface::class);
        $operation = 'get';
        $arguments = (new OperationArguments($operation))->setKey($key);
        $returnVal = 'bar';
        $exception = new Exception();

        $event = new OperationEvent($eventName, $storage, $operation, $arguments, $returnVal, $exception);

        $this->assertSame($eventName, $event->getName());
        $this->assertSame($storage, $event->getTarget());
        $this->assertSame($operation, $event->getOperation());
        $this->assertSame($arguments, $event->getArguments());
        $this->assertSame($returnVal, $event->getReturnValue());
        $this->assertSame($exception, $event->getException());
    }

    public function testSettersAndGetters()
    {
        $event = new OperationEvent(
            'unusedEvent',
            $this->getMockForAbstractClass(StorageInterface::class),
            'differentOperation',
            $this->getMockBuilder(OperationArguments::class)->disableOriginalConstructor()->getMock()
        );

        $key       = 'foo';
        $operation = 'get';
        $arguments = (new OperationArguments($operation))->setKey($key);
        $returnVal = 'bar';
        $exception = new Exception();

        $event->setOperation($operation);
        $event->setArguments($arguments);
        $event->setReturnValue($returnVal);
        $event->setException($exception);

        $this->assertSame($operation, $event->getOperation());
        $this->assertSame($arguments, $event->getArguments());
        $this->assertSame($returnVal, $event->getReturnValue());
        $this->assertSame($exception, $event->getException());
    }

    public function testReturnValueReference()
    {
        $key   = 'foo';
        $event = new OperationEvent(
            'beforeOperation',
            $this->getMockForAbstractClass(StorageInterface::class),
            'get',
            (new OperationArguments('get'))->setKey($key)
        );

        $returnValueA = 'foo';
        $event->setReturnValue($returnValueA);

        $returnValueB = & $event->getReturnValue();
        $returnValueB = 'bar';

        $this->assertSame($returnValueB, $returnValueA);
    }
}
