<?php
namespace Gamegos\NoSql\Tests\Storage;

/* Import from gamegos/nosql */
use Gamegos\NoSql\Storage\Memory;

/**
 * Test Class for Storage\Memory
 * @covers Gamegos\NoSql\Storage\Memory
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class MemoryTest extends AbstractCommonStorageTest
{
    /**
     * {@inheritdoc}
     * @return \Gamegos\NoSql\Storage\Memory
     */
    public function createStorage()
    {
        return new Memory();
    }
}
