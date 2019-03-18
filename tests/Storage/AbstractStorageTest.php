<?php
namespace Gamegos\NoSql\Tests\Storage;

/* Imports from PHPUnit */
use PHPUnit_Framework_TestCase;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\AbstractStorage;
use Gamegos\NoSql\Storage\OperationArguments;
use Gamegos\NoSql\Storage\Exception\InvalidKeyException;
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\Event\OperationListenerInterface;

/* Imports from PHP core */
use ReflectionMethod;
use Exception;

/**
 * Test Class for AbstractStorage
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class AbstractStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider \Gamegos\NoSql\Tests\TestAsset\DataProvider\OperationParamsProvider::getData
     * @param string $operation
     * @param array $args
     * @param mixed $result
     */
    public function testOperationsShouldCallDoOperation($operation, array $args, $result)
    {
        // Prepare the arguments.
        $arguments = new OperationArguments($operation, $args);

        // Create mock objects.
        $storage = $this->getMockBuilder(AbstractStorage::class)
            ->setMethods(['doOperation'])
            ->getMockForAbstractClass();

        // 'doOperation()' method should be called once.
        $mocker = $storage->expects($this->once())->method('doOperation')->with($operation, $arguments);

        // Conditional tests depending on the value of the $result parameter.
        if ($result instanceof Exception) {
            // The method throws an exception in this case.
            $exception = $result;
            $result    = null;
            $mocker->willThrowException($exception);
            // The exception thrown in the internal method should be forwarded to the upper segment.
            $this->setExpectedException(get_class($exception));
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
     * @testdox doOperation() should call internal method and fire operation events
     * @dataProvider \Gamegos\NoSql\Tests\TestAsset\DataProvider\OperationParamsProvider::getData
     * @param string $operation
     * @param array $args
     * @param mixed $result
     * @throws \ReflectionException
     */
    public function testDoOperation($operation, array $args, $result)
    {
        // Prepare the arguments.
        $arguments = new OperationArguments($operation, $args);

        // Create mock objects.
        $storage  = $this->getMockForAbstractClass(AbstractStorage::class);
        $listener = $this->getMockForAbstractClass(OperationListenerInterface::class);
        $storage->addOperationListener($listener);

        // 'beforeOperation' should be triggered once.
        $listener->expects($this->once())->method('beforeOperation')->with(
            new OperationEvent('beforeOperation', $storage, $operation, $arguments)
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
            $this->setExpectedException(get_class($exception));
            // 'onOperationException' should be triggered once.
            $listener->expects($this->once())->method('onOperationException')->with(
                new OperationEvent('onOperationException', $storage, $operation, $arguments, $result, $exception)
            );
            // Run the operation.
            $this->callDoOperation($storage, $operation, $arguments);
        } else {
            // Internal method returns a value in this case.
            $mocker->willReturn($result);
            // 'afterOperation' should be triggered once.
            $listener->expects($this->once())->method('afterOperation')->with(
                new OperationEvent('afterOperation', $storage, $operation, $arguments, $result)
            );
            // Run the operation and validate the returned value.
            $this->assertSame($result, $this->callDoOperation($storage, $operation, $arguments));
        }
    }

    /**
     * Data provider for invalid NoSql keys.
     * @return array
     */
    public function invalidKeyProvider()
    {
        return [
            'null'         => [null],
            'bool-true'    => [true],
            'bool-false'   => [false],
            'int'          => [1],
            'int-zero'     => [0],
            'float'        => [1.1],
            'array'        => [['foo' => 'bar']],
            'array-empty'  => [[]],
            'object'       => [(object) ['foo' => 'bar']],
            'resource'     => [fopen('php://memory', 'r')],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidKeyProvider
     * @testdox doOperation() should throw InvalidKeyException on invalid key
     */
    public function testDoOperationShouldThrowInvalidKeyExceptionOnInvalidKey($key)
    {
        $this->setExpectedException(InvalidKeyException::class);

        $storage   = $this->getMockForAbstractClass(AbstractStorage::class);
        $operation = 'has';
        $arguments = (new OperationArguments($operation))->setKey($key);

        $this->callDoOperation($storage, $operation, $arguments);
    }

    /**
     * @dataProvider invalidKeyProvider
     * @testdox doOperation() should throw InvalidKeyException if one of the keys is invalid
     */
    public function testDoOperationShouldThrowInvalidKeyExceptionOnInvalidKeys($key)
    {
        $keys = [$key];
        $this->setExpectedException(InvalidKeyException::class);

        $storage   = $this->getMockForAbstractClass(AbstractStorage::class);
        $operation = 'getMulti';
        $arguments = (new OperationArguments($operation))->setKeys($keys);

        $this->callDoOperation($storage, $operation, $arguments);
    }

    /**
     * Call the protected AbstractStorage::doOperation() method.
     * @param  \Gamegos\NoSql\Storage\AbstractStorage $storage
     * @param  string $operation
     * @param  \Gamegos\NoSql\Storage\OperationArguments $args
     * @return mixed
     * @throws \ReflectionException
     */
    protected function callDoOperation(AbstractStorage $storage, $operation, OperationArguments $args)
    {
        $rm = new ReflectionMethod(AbstractStorage::class, 'doOperation');
        return call_user_func($rm->getClosure($storage), $operation, $args);
    }
}
