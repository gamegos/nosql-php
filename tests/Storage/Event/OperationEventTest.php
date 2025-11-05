<?php
namespace Gamegos\NoSql\Tests\Storage\Event;

use Exception;
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\StorageInterface;
use Gamegos\NoSql\Storage\OperationArguments;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test Class for OperationEvent
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationEventTest extends TestCase
{
    #[Test]
    #[TestDox('Constructor should set all properties correctly')]
    public function constructorArguments()
    {
        $key       = 'foo';
        $eventName = 'beforeOperation';
        $storage   = $this->createMock(StorageInterface::class);
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

    #[Test]
    #[TestDox('Getters and Setters should work correctly')]
    public function testSettersAndGetters()
    {
        $event = new OperationEvent(
            'unusedEvent',
            $this->createMock(StorageInterface::class),
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

    #[Test]
    #[TestDox('Return value reference should work correctly')]
    public function returnValueReference()
    {
        $key   = 'foo';
        $event = new OperationEvent(
            'beforeOperation',
            $this->createMock(StorageInterface::class),
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
