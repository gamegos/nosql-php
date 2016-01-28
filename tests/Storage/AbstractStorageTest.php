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
     * Data provider for various operation cases.
     * @return array
     */
    public function operationParamsProvider()
    {
        return [
            // Test has() method with 1 argument.
            'has with 1 arg'           => [
                'has',
                [
                    'key' => 'foo',
                ],
                true,
            ],
            // Test has() method with exception.
            'has with exception'       => [
                'has',
                [
                    'key' => 'foo',
                ],
                new Exception(),
            ],
            // Test get() method with 1 argument.
            'get with 1 arg'           => [
                'get',
                [
                    'key' => 'foo',
                ],
                'bar',
            ],
            // Test get() method with 2 arguments.
            'get with 2 args'          => [
                'get',
                [
                    'key'      => 'foo',
                    'casToken' => null,
                ],
                'bar',
            ],
            // Test get() method with exception.
            'get with exception'       => [
                'get',
                [
                    'key' => 'foo',
                ],
                new Exception(),
            ],
            // Test getMulti() method with 1 argument.
            'getMulti with 1 arg'      => [
                'getMulti',
                [
                    'keys' => ['foo1', 'foo2', 'foo3'],
                ],
                ['bar1', 'bar2', 'bar3'],
            ],
            // Test getMulti() method with 2 arguments.
            'getMulti with 2 args'     => [
                'getMulti',
                [
                    'keys'      => ['foo1', 'foo2', 'foo3'],
                    'casTokens' => ['foo1' => null, 'foo2' => null, 'foo3' => null],
                ],
                ['bar1', 'bar2', 'bar3'],
            ],
            // Test getMulti() method with exception.
            'getMulti with exception'  => [
                'getMulti',
                [
                    'keys' => ['foo1', 'foo2', 'foo3'],
                ],
                new Exception(),
            ],
            // Test add() method with 2 arguments.
            'add with 2 args'          => [
                'add',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                true,
            ],
            // Test add() method with 3 arguments.
            'add with 3 args'          => [
                'add',
                [
                    'key'    => 'foo',
                    'value'  => 'bar',
                    'expiry' => 100,
                ],
                true,
            ],
            // Test add() method with exception.
            'add with exception'       => [
                'add',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                new Exception(),
            ],
            // Test set() method with 2 arguments.
            'set with 2 args'          => [
                'set',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                true,
            ],
            // Test set() method with 3 arguments.
            'set with 3 args'          => [
                'set',
                [
                    'key'    => 'foo',
                    'value'  => 'bar',
                    'expiry' => 100,
                ],
                true,
            ],
            // Test set() method with 4 arguments.
            'set with 4 args'          => [
                'set',
                [
                    'key'      => 'foo',
                    'value'    => 'bar',
                    'expiry'   => 100,
                    'casToken' => null,
                ],
                true,
            ],
            // Test set() method with exception.
            'set with exception'       => [
                'set',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                new Exception(),
            ],
            // Test cas() method with 3 arguments.
            'cas with 3 args'          => [
                'cas',
                [
                    'casToken' => null,
                    'key'      => 'foo',
                    'value'    => 'bar',
                ],
                true,
            ],
            // Test cas() method with 4 arguments.
            'cas with 4 args'          => [
                'cas',
                [
                    'casToken' => null,
                    'key'      => 'foo',
                    'value'    => 'bar',
                    'expiry'   => 100,
                ],
                true,
            ],
            // Test cas() method with exception.
            'cas with exception'       => [
                'cas',
                [
                    'casToken' => null,
                    'key'      => 'foo',
                    'value'    => 'bar',
                ],
                new Exception(),
            ],
            // Test delete() method with 1 argument.
            'delete with 1 args'       => [
                'delete',
                [
                    'key' => 'foo',
                ],
                true,
            ],
            // Test delete() method with exception.
            'delete with exception'    => [
                'delete',
                [
                    'key' => 'foo',
                ],
                new Exception(),
            ],
            // Test append() method with 2 arguments.
            'append with 2 args'       => [
                'append',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                true,
            ],
            // Test append() method with 3 arguments.
            'append with 3 args'       => [
                'append',
                [
                    'key'    => 'foo',
                    'value'  => 'bar',
                    'expiry' => 100,
                ],
                true,
            ],
            // Test append() method with exception.
            'append with exception'    => [
                'append',
                [
                    'key'   => 'foo',
                    'value' => 'bar',
                ],
                new Exception(),
            ],
            // Test increment() method with 1 argument.
            'increment with 1 arg'     => [
                'increment',
                [
                    'key' => 'foo',
                ],
                3,
            ],
            // Test increment() method with 2 arguments.
            'increment with 2 args'    => [
                'increment',
                [
                    'key'    => 'foo',
                    'offset' => 2,
                ],
                3,
            ],
            // Test increment() method with 3 arguments.
            'increment with 3 args'    => [
                'increment',
                [
                    'key'     => 'foo',
                    'offset'  => 2,
                    'initial' => 1,
                ],
                3,
            ],
            // Test increment() method with 4 arguments.
            'increment with 4 args'    => [
                'increment',
                [
                    'key'     => 'foo',
                    'offset'  => 2,
                    'initial' => 1,
                    'expiry'  => 100,
                ],
                3,
            ],
            // Test increment() method with exception.
            'increment with exception' => [
                'increment',
                [
                    'key' => 'foo',
                ],
                new Exception(),
            ],
        ];
    }

    /**
     * @dataProvider operationParamsProvider
     * @param string $operation
     * @param array $args
     * @param mixed $result
     */
    public function testOperationsShouldCallDoOperation($operation, array $args, $result)
    {
        // Prepare the arguments.
        $arguments = new OperationArguments($operation);
        foreach ($args as $argument => & $value) {
            $arguments->set($argument, $value);
        }
        unset($value);

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
     * @dataProvider operationParamsProvider
     * @param string $operation
     * @param array $args
     * @param mixed $result
     */
    public function testDoOperation($operation, array $args, $result)
    {
        // Prepare the arguments.
        $arguments = new OperationArguments($operation);
        foreach ($args as $argument => & $value) {
            $arguments->set($argument, $value);
        }
        unset($value);

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
     */
    protected function callDoOperation(AbstractStorage $storage, $operation, OperationArguments $args)
    {
        $rm = new ReflectionMethod(AbstractStorage::class, 'doOperation');
        return call_user_func($rm->getClosure($storage), $operation, $args);
    }
}
