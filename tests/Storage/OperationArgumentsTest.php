<?php
namespace Gamegos\NoSql\Tests\Storage;

use Gamegos\NoSql\Storage\OperationArguments;
use Gamegos\NoSql\Storage\Exception\OperationArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use OutOfRangeException;

/**
 * Test Class for OperationArguments
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationArgumentsTest extends TestCase
{
    #[Test]
    #[TestDox('validateArgument() should throw exception with data set "$_dataName"')]
    #[DataProvider('invalidArgumentProvider')]
    public function validateArgumentThrowsExceptionForInvalidArgument(mixed $value, string $expectType, bool $nullable)
    {
        $arguments = new OperationArguments('operationX');
        $this->expectException(OperationArgumentException::class);
        $arguments->validateArgument('arg', $value, $expectType, $nullable);
    }

    #[Test]
    #[TestDox('validateArrayArgument() should throw exception with data set "$_dataName"')]
    #[DataProvider('invalidArgumentProvider')]
    public function validateArrayArgumentThrowsExceptionForInvalidArgument(mixed $value, string $expectType, bool $nullable)
    {
        $arguments = new OperationArguments('operationX');
        $this->expectException(OperationArgumentException::class);
        $arguments->validateArrayArgument('arg', [$value], $expectType, $nullable);
    }

    #[Test]
    #[TestDox('get() should throw exception for undefined argument')]
    public function getShouldThrowExceptionForUndefinedArgument()
    {
        $realArgs  = ['foo' => 'bar'];
        $undefined = 'baz';
        $arguments = new OperationArguments('operationX', $realArgs);
        $this->expectExceptionObject(new OutOfRangeException(sprintf('Argument %s does not exist.', $undefined)));
        $arguments->get($undefined);
    }

    public static function invalidArgumentProvider(): array
    {
        $acceptTypes = [
            'boolean',
            'integer',
            'double',
            'string',
            'array',
            'object',
            'resource',
        ];
        $sampleData  = [
            'null'        => null,
            'bool-true'   => true,
            'bool-false'  => false,
            'int'         => 1,
            'int-zero'    => 0,
            'float'       => 1.1,
            'string'      => '',
            'array'       => ['foo' => 'bar'],
            'array-empty' => [],
            'object'      => (object) ['foo' => 'bar'],
            'resource'    => fopen('php://memory', 'r'),
        ];
        $data = [];
        foreach ($acceptTypes as $expectType) {
            foreach ($sampleData as $typeSet => $value) {
                if (gettype($value) != $expectType) {
                    $data[sprintf('%s-%s', $expectType, $typeSet)] = [$value, $expectType, false];
                    if ($value !== null) {
                        $data[sprintf('%s(nullable)-%s', $expectType, $typeSet)] = [$value, $expectType, true];
                    }
                }
            }
        }
        return $data;
    }
}
