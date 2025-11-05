<?php
namespace Gamegos\NoSql\Tests\TestAsset\DataProvider;

use Exception;

/**
 * Operation Params Data Provider
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationParamsProvider
{
    /**
     * Get data.
     * @return array
     */
    public static function getData(): array
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
                    'casToken' => 'test-token',
                    'key'      => 'foo',
                    'value'    => 'bar',
                ],
                true,
            ],
            // Test cas() method with 4 arguments.
            'cas with 4 args'          => [
                'cas',
                [
                    'casToken' => 'test-token',
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
                    'casToken' => 'test-token',
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
}
