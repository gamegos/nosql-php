<?php
namespace Gamegos\NoSql\Tests\Storage;

use Gamegos\NoSql\Storage\Memory;
use Gamegos\NoSql\Storage\StorageInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test Class for Storage\Memory
 * @author Safak Ozpinar <safak@gamegos.com>
 */
#[CoversClass(Memory::class)]
class MemoryTest extends AbstractCommonStorageTestCase
{
    /**
     * {@inheritdoc}
     * @return \Gamegos\NoSql\Storage\Memory
     */
    public function createStorage(): StorageInterface
    {
        return new Memory();
    }
}
