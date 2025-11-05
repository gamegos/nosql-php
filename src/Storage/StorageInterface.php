<?php
namespace Gamegos\NoSql\Storage;

/**
 * Interface for NoSql Storages
 * @package Gamegos\NoSql
 * @author  Safak Ozpinar <safak@gamegos.com>
 */
interface StorageInterface
{
    /**
     * Check if a key exists in the storage.
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get a value from the storage.
     * @param  string $key
     * @param  string|null $casToken
     * @return mixed
     */
    public function get(string $key, ?string &$casToken = null): mixed;

    /**
     * Get multiple values from the storage.
     * @param  array<string> $keys
     * @param  array<string>|null $casTokens
     * @return array<string, mixed>
     */
    public function getMulti(array $keys, ?array &$casTokens = null): array;

    /**
     * Add a value under a new key.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     */
    public function add(string $key, mixed $value, int $expiry = 0): bool;

    /**
     * Store/update a value in the storage.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @param  string|null $casToken
     * @return bool
     */
    public function set(string $key, mixed $value, int $expiry = 0, ?string $casToken = null): bool;

    /**
     * Compare and swap (CAS) a value in the storage.
     * @param  string $casToken
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    StorageInterface::get()
     * @see    StorageInterface::getMulti()
     */
    public function cas(string $casToken, string $key, mixed $value, int $expiry = 0): bool;

    /**
     * Delete a value from the storage.
     * @param  string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Append a value to an existing value.
     * @param  string $key
     * @param  string $value
     * @param  int $expiry
     * @return bool
     */
    public function append(string $key, string $value, int $expiry = 0): bool;

    /**
     * Increment value of a numeric entry.
     * @param  string $key
     * @param  int $offset
     * @param  int $initial
     * @param  int $expiry
     * @return int|bool
     */
    public function increment(string $key, int $offset = 1, int $initial = 0, int $expiry = 0): int|bool;
}
