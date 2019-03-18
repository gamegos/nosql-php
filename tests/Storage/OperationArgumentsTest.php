<?php
namespace Gamegos\NoSql\Tests\Storage;

/* Imports from PHPUnit */
use PHPUnit_Framework_TestCase;

/* Import from gamegos/nosql */
use Gamegos\NoSql\Storage\OperationArguments;
use Gamegos\NoSql\Storage\Exception\OperationArgumentException;

/* Imports from PHP core */
use OutOfRangeException;

/**
 * Test Class for OperationArguments
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationArgumentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $value
     * @param $expectType
     * @param $nullable
     * @dataProvider invalidArgumentProvider
     */
    public function testValidateArgumentThrowsExceptionForInvalidArgument($value, $expectType, $nullable)
    {
        $arguments = new OperationArguments('operationX');
        $this->setExpectedException(OperationArgumentException::class);
        $arguments->validateArgument('arg', $value, $expectType, $nullable);
    }

    public function testGetShouldThrowExceptionForUndefinedArgument()
    {
        $realArgs  = ['foo' => 'bar'];
        $undefined = 'baz';
        $arguments = new OperationArguments('operationX', $realArgs);
        $this->setExpectedException(OutOfRangeException::class, sprintf('Argument %s does not exist.', $undefined));
        $arguments->get($undefined);
    }

    public function invalidArgumentProvider()
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
