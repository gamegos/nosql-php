<?php
namespace Gamegos\NoSql\Tests\Storage;

/* Imports from PHPUnit */
use PHPUnit_Framework_TestCase;

/* Imports from PHP core */
use RuntimeException;

/**
 * Base Test Class for Storages
 * @author Safak Ozpinar <safak@gamegos.com>
 */
abstract class AbstractCommonStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox set(), has(), get() and delete()
     */
    public function testSetHasGetAndDelete()
    {
        $key   = 'foo';
        $value = 'bar';

        $storage = $this->createStorage();

        $this->assertFalse($storage->has($key));
        $this->assertNull($storage->get($key));

        $this->assertTrue($storage->set($key, $value));

        $this->assertTrue($storage->has($key));
        $this->assertEquals($value, $storage->get($key));

        $this->assertTrue($storage->delete($key));

        $this->assertFalse($storage->has($key));
        $this->assertNull($storage->get($key));

        $this->assertFalse($storage->delete($key));
    }

    /**
     * @testdox add() should succeed for new key
     */
    public function testAddShouldSucceedForNewKey()
    {
        $key     = 'foo';
        $value   = 'bar';
        $storage = $this->createStorage();

        $this->assertTrue($storage->add($key, $value));
        $this->assertTrue($storage->has($key));
        $this->assertEquals($value, $storage->get($key));

        return [
            'storage' => $storage,
            'key'     => $key,
        ];
    }

    /**
     * @testdox add() should fail for existing key
     */
    public function testAddShouldFailForExistingKey()
    {
        $params  = $this->testAddShouldSucceedForNewKey();
        $storage = $params['storage'];
        $key     = $params['key'];

        $this->assertFalse($storage->add($key, 'baz'));
    }

    /**
     * @testdox add(), set() and has() with expiry
     */
    public function testAddSetAndHasWithExpiry()
    {
        $storage = $this->createStorage();

        $this->assertTrue($storage->set('key1', 'value1', 1));
        $this->assertTrue($storage->has('key1'));
        $this->assertEquals('value1', $storage->get('key1'));

        $this->assertTrue($storage->add('key2', 'value2', 1));
        $this->assertTrue($storage->has('key2'));
        $this->assertEquals('value2', $storage->get('key2'));

        sleep(2);

        $this->assertFalse($storage->has('key1'));
        $this->assertFalse($storage->has('key2'));
        $this->assertNull($storage->get('key1'));
        $this->assertNull($storage->get('key2'));
    }

    public function testGetMulti()
    {
        $items = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $storage = $this->createStorage();

        foreach ($items as $key => $value) {
            $this->assertTrue($storage->set($key, $value));
        }

        $this->assertEquals($items, $storage->getMulti(array_keys($items)));

        return [
            'storage' => $storage,
            'items'   => $items,
        ];
    }

    /**
     * @testdox getMulti() with CAS token
     */
    public function testGetMultiWithCasToken()
    {
        $params = $this->testGetMulti();
        /* @var $storage \Gamegos\NoSql\Storage\StorageInterface */
        $storage   = $params['storage'];
        $items     = $params['items'];
        $keys      = array_keys($items);
        $casTokens = [];

        $this->assertEquals($items, $storage->getMulti($keys, $casTokens));
        $this->assertCount(count($items), $casTokens);

        $newValues = [];
        foreach ($items as $key => $value) {
            $this->assertArrayHasKey($key, $casTokens);
            $casToken = $casTokens[$key];
            $this->assertInternalType('string', gettype($casToken));

            $value .= '.baz';

            $this->assertTrue($storage->cas($casToken, $key, $value));
            $this->assertEquals($value, $storage->get($key));

            $newValues[$key] = $value;
        }

        $casTokens = [];
        $this->assertEquals($newValues, $storage->getMulti($keys, $casTokens));
    }

    public function testAppend()
    {
        $key  = 'foo';
        $str1 = 'bar';
        $str2 = 'baz';

        $storage = $this->createStorage();

        $this->assertTrue($storage->append($key, $str1));
        $this->assertTrue($storage->append($key, $str2));

        $this->assertEquals($str1 . $str2, $storage->get($key));
    }

    /**
     * Data provider for non-string values.
     * @return array
     */
    public function nonStringProvider()
    {
        return [
            'null'        => [null],
            'bool-true'   => [true],
            'bool-false'  => [false],
            'int'         => [1],
            'int-zero'    => [0],
            'float'       => [1.1],
            'array'       => [['foo' => 'bar']],
            'array-empty' => [[]],
            'object'      => [(object) ['foo' => 'bar']],
            'resource'    => [fopen('php://memory', 'r')],
        ];
    }

    /**
     * @testdox      append() should throw RuntimeException if existing value is not string
     * @dataProvider nonStringProvider
     */
    public function testAppendShouldThrowExceptionForNonStringExistingValue($value)
    {
        $storage = $this->createStorage();
        $storage->set('foo', $value);

        $this->setExpectedException(RuntimeException::class);
        $storage->append('foo', 'bar');
    }

    public function testIncrement()
    {
        $key     = 'foo';
        $initial = 1000;
        $offset  = 100;

        $storage = $this->createStorage();
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals($initial + $offset * $i, $storage->increment($key, $offset, $initial));
        }
    }

    /**
     * Data provider for non-integer values.
     * @return array
     */
    public function nonIntegerProvider()
    {
        return [
            'null'        => [null],
            'bool-true'   => [true],
            'bool-false'  => [false],
            'float'       => [1.1],
            'string'      => [''],
            'array'       => [['foo' => 'bar']],
            'array-empty' => [[]],
            'object'      => [(object) ['foo' => 'bar']],
            'resource'    => [fopen('php://memory', 'r')],
        ];
    }

    /**
     * @testdox      increment() should throw RuntimeException if existing value is not integer
     * @dataProvider nonIntegerProvider
     */
    public function testIncrementShouldThrowExceptionForNonIntegerExistingValue($value)
    {
        $storage = $this->createStorage();
        $storage->set('foo', $value);

        $this->setExpectedException(RuntimeException::class);
        $storage->increment('foo');
    }

    /**
     * @testdox get() and set() with CAS token
     */
    public function testGetAndSetWithCasToken()
    {
        $key      = 'foo';
        $value    = 'bar';
        $casToken = null;

        $storage = $this->createStorage();

        $this->assertTrue($storage->set($key, $value, 0, $casToken));

        $this->assertEquals($value, $storage->get($key, $casToken));
        $this->assertInternalType('string', gettype($casToken));

        $value = 'baz';
        $this->assertTrue($storage->set($key, $value, 0, $casToken));
        $this->assertEquals($value, $storage->get($key, $casToken));

        $value    = 'bum';
        $casToken = 'invalid cas token';
        $this->assertFalse($storage->set($key, $value, 0, $casToken));
    }

    /**
     * @testdox Compare and swap
     */
    public function testCas()
    {
        $key      = 'foo';
        $value    = 'bar';
        $casToken = null;
        $storage  = $this->createStorage();

        $this->assertTrue($storage->set($key, $value));

        $this->assertEquals($value, $storage->get($key, $casToken));
        $this->assertInternalType('string', gettype($casToken));

        $value = 'baz';
        $this->assertTrue($storage->cas($casToken, $key, $value));
        $this->assertEquals($value, $storage->get($key, $casToken));

        $value    = 'bum';
        $casToken = 'invalid cas token';
        $this->assertFalse($storage->cas($casToken, $key, $value));
    }

    /**
     * Create and return a storage.
     * @return \Gamegos\NoSql\Storage\StorageInterface
     */
    abstract public function createStorage();
}
