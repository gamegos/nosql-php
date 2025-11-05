<?php
namespace Gamegos\NoSql\Tests\Storage;

use Gamegos\NoSql\Storage\StorageInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Base Test Class for Storages
 * @author Safak Ozpinar <safak@gamegos.com>
 */
abstract class AbstractCommonStorageTestCase extends TestCase
{
    #[Test]
    #[TestDox('set(), has(), get() and delete()')]
    public function setHasGetAndDelete()
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

    #[Test]
    #[TestDox('add() new key')]
    public function addNewKey(): array
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

    #[Test]
    #[TestDox('add() should fail for existing key')]
    #[Depends('addNewKey')]
    public function addShouldFailForExistingKey(array $params)
    {
        $storage = $params['storage'];
        $key     = $params['key'];

        $this->assertFalse($storage->add($key, 'baz'));
    }

    #[Test]
    #[TestDox('add(), set() and has() with expiry')]
    public function addSetAndHasWithExpiry()
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

    #[Test]
    #[TestDox('getMulti()')]
    public function getMulti(): array
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

    #[Test]
    #[TestDox('getMulti() with CAS token')]
    #[Depends('getMulti')]
    public function getMultiWithCasToken(array $params)
    {
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
            $this->assertIsString($casToken);

            $value .= '.baz';

            $this->assertTrue($storage->cas($casToken, $key, $value));
            $this->assertEquals($value, $storage->get($key));

            $newValues[$key] = $value;
        }

        $casTokens = [];
        $this->assertEquals($newValues, $storage->getMulti($keys, $casTokens));
    }

    #[Test]
    #[TestDox('append()')]
    public function append()
    {
        $key  = 'foo';
        $str1 = 'bar';
        $str2 = 'baz';

        $storage = $this->createStorage();

        $this->assertTrue($storage->append($key, $str1));
        $this->assertTrue($storage->append($key, $str2));

        $this->assertEquals($str1 . $str2, $storage->get($key));
    }

    #[Test]
    #[TestDox('append() with string existing value')]
    public function appendWithStringValue()
    {
        $storage = $this->createStorage();
        $storage->set('foo', 'initial');

        $this->assertTrue($storage->append('foo', '.appended'));
        $this->assertEquals('initial.appended', $storage->get('foo'));
    }

    #[Test]
    #[TestDox('append() should throw exception with data set "$_dataName"')]
    #[DataProvider('nonStringProvider')]
    public function appendShouldThrowExceptionForNonStringExistingValue($value)
    {
        $storage = $this->createStorage();
        $storage->set('foo', $value);

        $this->expectException(RuntimeException::class);
        $storage->append('foo', 'bar');
    }

    public static function nonStringProvider(): array
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

    #[Test]
    #[TestDox('increment()')]
    public function increment()
    {
        $key     = 'foo';
        $initial = 1000;
        $offset  = 100;

        $storage = $this->createStorage();
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame($initial + $offset * $i, $storage->increment($key, $offset, $initial));
        }
    }

    #[Test]
    #[TestDox('increment() with integer existing value')]
    public function incrementWithIntegerValue()
    {
        $storage = $this->createStorage();
        $storage->set('foo', 10);

        $this->assertEquals(11, $storage->increment('foo'));
        $this->assertEquals(11, $storage->get('foo'));
    }

    #[Test]
    #[TestDox('increment() should throw exception with data set "$_dataName"')]
    #[DataProvider('nonIntegerProvider')]
    public function incrementShouldThrowExceptionForNonIntegerExistingValue($value)
    {
        $storage = $this->createStorage();
        $storage->set('foo', $value);

        $this->expectException(RuntimeException::class);
        $storage->increment('foo');
    }

    /**
     * Data provider for non-integer values.
     * @return array
     */
    public static function nonIntegerProvider(): array
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

    #[Test]
    #[TestDox('get() and set() with CAS token')]
    public function testGetAndSetWithCasToken()
    {
        $key      = 'foo';
        $value    = 'bar';
        $casToken = null;

        $storage = $this->createStorage();

        $this->assertTrue($storage->set($key, $value, 0, $casToken));

        $this->assertEquals($value, $storage->get($key, $casToken));
        $this->assertIsString($casToken);

        $value = 'baz';
        $this->assertTrue($storage->set($key, $value, 0, $casToken));
        $this->assertEquals($value, $storage->get($key, $casToken));

        $value    = 'bum';
        $casToken = 'invalid cas token';
        $this->assertFalse($storage->set($key, $value, 0, $casToken));
    }

    #[Test]
    #[TestDox('Compare and swap (CAS)')]
    public function cas()
    {
        $key      = 'foo';
        $value    = 'bar';
        $casToken = null;
        $storage  = $this->createStorage();

        $storage->set($key, $value);

        $this->assertEquals($value, $storage->get($key, $casToken));
        $this->assertIsString($casToken);

        $value = 'baz';
        $this->assertTrue($storage->cas($casToken, $key, $value));
        $this->assertEquals($value, $storage->get($key, $casToken));

        $value    = 'bum';
        $casToken = 'invalid cas token';
        $this->assertFalse($storage->cas($casToken, $key, $value));
    }

    #[Test]
    #[TestDox('cas() should fail for non-existing key')]
    public function casWithNonExisting()
    {
        $key      = 'foo';
        $value    = 'bar';
        $casToken = 'invalid cas token';
        $storage  = $this->createStorage();

        $this->assertFalse($storage->cas($casToken, $key, $value));
    }

    /**
     * Create and return a storage.
     * @return \Gamegos\NoSql\Storage\StorageInterface
     */
    abstract public function createStorage(): StorageInterface;
}
