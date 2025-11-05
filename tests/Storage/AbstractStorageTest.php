<?php
namespace Gamegos\NoSql\Tests\Storage;

use Exception;
use Gamegos\NoSql\Storage\AbstractStorage;
use Gamegos\NoSql\Storage\OperationArguments;
use Gamegos\NoSql\Storage\Exception\InvalidKeyException;
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\Event\OperationListenerInterface;
use Gamegos\NoSql\Tests\TestAsset\DataProvider\OperationParamsProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Test Class for AbstractStorage
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class AbstractStorageTest extends TestCase
{
    public function getAbstractStorageMock(string ...$additionalMethods): MockObject|AbstractStorage
    {
        $rClass  = new ReflectionClass(AbstractStorage::class);
        $methods = array_map(fn($method) => $method->getName(), $rClass->getMethods(ReflectionMethod::IS_ABSTRACT));
        return $this->getMockBuilder(AbstractStorage::class)->onlyMethods(array_merge($methods, $additionalMethods))->getMock();
    }

    /**
     * @param string $operation
     * @param array $args
     * @param mixed $result
     */
    #[Test]
    #[TestDox('$operation() should call executeOperation() with data set "$_dataName"')]
    #[DataProviderExternal(OperationParamsProvider::class, 'getData')]
    public function operationShouldCallExecuteOperation(string $operation, array $args, mixed $result)
    {
        // Prepare the internal arguments.
        $operationArguments = new OperationArguments($operation, $args);

        // Create mock objects.
        $storage = $this->getAbstractStorageMock('executeOperation');

        // 'executeOperation()' method should be called once.
        $mocker = $storage->expects($this->once())->method('executeOperation')->with($operation, $this->isCallable(), $operationArguments);

        // Conditional tests depending on the value of the $result parameter.
        if ($result instanceof Exception) {
            // The method throws an exception in this case.
            $exception = $result;
            $mocker->willThrowException($exception);
            // The exception thrown in the internal method should be forwarded to the upper segment.
            $this->expectException(get_class($exception));
            // Run the operation.
            call_user_func_array([$storage, $operation], $args);
        } else {
            // The method returns a value in this case.
            $mocker->willReturn($result);
            // Run the operation and validate the returned value.
            $this->assertSame($result, call_user_func_array([$storage, $operation], $args));
        }
    }

    /**
     * @param $operation
     * @param  array $args
     * @param $result
     * @throws \ReflectionException
     */
    #[Test]
    #[TestDox('executeOperation() with data set "$_dataName"')]
    #[DataProviderExternal(OperationParamsProvider::class, 'getData')]
    public function executeOperation($operation, array $args, $result)
    {
        // Prepare the arguments.
        $operationArguments = new OperationArguments($operation, $args);

        // Create mock objects.
        $storage  = $this->getAbstractStorageMock();
        $listener = $this->createMock(OperationListenerInterface::class);
        $storage->addOperationListener($listener);

        // 'beforeOperation' should be triggered once.
        $listener->expects($this->once())->method('beforeOperation')->with(
            new OperationEvent('beforeOperation', $storage, $operation, $operationArguments)
        );

        // Internal operation method should be called once.
        $mocker = $storage->expects($this->once())->method($operation . 'Internal');
        $mocker = call_user_func_array([$mocker, 'with'], $args);

        // Conditional tests depending on the value of the $result parameter.
        if ($result instanceof Exception) {
            // Internal method throws an exception in this case.
            $exception = $result;
            $result    = null;
            $mocker->willThrowException($exception);
            // The exception thrown in the internal method should be forwarded to the upper segment.
            $this->expectException(get_class($exception));
            // 'onOperationException' should be triggered once.
            $listener->expects($this->once())->method('onOperationException')->with(
                new OperationEvent('onOperationException', $storage, $operation, $operationArguments, $result, $exception)
            );
            // Run the operation.
            call_user_func_array([$storage, $operation], $args);
        } else {
            // Internal method returns a value in this case.
            $mocker->willReturn($result);
            // 'afterOperation' should be triggered once.
            $listener->expects($this->once())->method('afterOperation')->with(
                new OperationEvent('afterOperation', $storage, $operation, $operationArguments, $result)
            );
            // Run the operation and validate the returned value.
            $this->assertSame($result, call_user_func_array([$storage, $operation], $args));
        }
    }

    /**
     * @throws \ReflectionException
     */
    #[Test]
    #[TestDox('executeOperation() should call internal method and fire operation events')]
    public function executeOperationCallsInternalMethod()
    {
        $operation = 'has';
        $key = 'foo';
        $expected = true;

        // Prepare the arguments.
        $operationArguments = (new OperationArguments($operation))->setKey($key);

        // Create mock objects.
        $storage  = $this->getAbstractStorageMock();
        $listener = $this->createMock(OperationListenerInterface::class);
        $storage->addOperationListener($listener);

        // 'beforeOperation' should be triggered once.
        $listener->expects($this->once())->method('beforeOperation')->with(
            new OperationEvent('beforeOperation', $storage, $operation, $operationArguments)
        );

        // Internal operation method should be called once.
        $storage->expects($this->once())->method('hasInternal')->with($this->identicalTo($key))->willReturn($expected);

        // 'afterOperation' should be triggered once.
        $listener->expects($this->once())->method('afterOperation')->with(
            new OperationEvent('afterOperation', $storage, $operation, $operationArguments, $expected)
        );

        $reflectHasInternal = new ReflectionMethod($storage::class, 'hasInternal');

        $reflectExecuteOperation = new ReflectionMethod(AbstractStorage::class, 'executeOperation');
        $actual = $reflectExecuteOperation->invoke($storage, $operation, fn() => $reflectHasInternal->invoke($storage, $key), $operationArguments);

        // Run the operation and validate the returned value.
        $this->assertSame($expected, $actual);
    }

    /**
     * @throws \ReflectionException
     */
    #[Test]
    #[TestDox('executeOperation() should throw InvalidKeyException if the key is empty')]
    public function executeOperationShouldThrowInvalidKeyExceptionOnEmptyKey()
    {
        $this->expectException(InvalidKeyException::class);

        $storage   = $this->getAbstractStorageMock();
        $operation = 'has';
        $key       = '';
        $arguments = (new OperationArguments($operation))->setKey($key);

        $reflectExecuteOperation = new ReflectionMethod(AbstractStorage::class, 'executeOperation');
        $reflectExecuteOperation->invoke($storage, $operation, fn() => true, $arguments);
    }

    /**
     * @throws \ReflectionException
     */
    #[Test]
    #[TestDox('executeOperation() should throw InvalidKeyException if one of the keys is empty')]
    public function executeOperationShouldThrowInvalidKeyExceptionOnEmptyKeys()
    {
        $this->expectException(InvalidKeyException::class);

        $storage   = $this->getAbstractStorageMock();
        $operation = 'getMulti';
        $keys      = [''];
        $arguments = (new OperationArguments($operation))->setKeys($keys);

        $reflectExecuteOperation = new ReflectionMethod(AbstractStorage::class, 'executeOperation');
        $reflectExecuteOperation->invoke($storage, $operation, fn() => true, $arguments);
    }
}
