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
    public function has($key);

    /**
     * Get a value from the storage.
     * @param  string $key
     * @param  string $casToken
     * @return mixed
     */
    public function get($key, & $casToken = null);

    /**
     * Get multiple values from the storage.
     * @param  array $keys
     * @param  array $casTokens
     * @return array
     */
    public function getMulti(array $keys, array & $casTokens = null);

    /**
     * Add an value under a new key.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     */
    public function add($key, $value, $expiry = 0);

    /**
     * Store/update a value in the storage.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @param  string $casToken
     * @return bool
     */
    public function set($key, $value, $expiry = 0, $casToken = null);

    /**
     * Store a value only if the token matches.
     * @param  string $token
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    StorageInterface::get()
     * @see    StorageInterface::getMulti()
     */
    public function cas($token, $key, $value, $expiry = 0);

    /**
     * Delete a value from the storage.
     * @param  string $key
     * @return bool
     */
    public function delete($key);

    /**
     * Append a value to an existing value.
     * @param  string $key
     * @param  string $value
     * @param  int $expiry
     * @return bool
     */
    public function append($key, $value, $expiry = 0);

    /**
     * Increment value of a numeric entry.
     * @param  string $key
     * @param  int $offset
     * @param  int $initial
     * @param  int $expiry
     * @return int|boolean
     */
    public function increment($key, $offset = 1, $initial = 0, $expiry = 0);
}
