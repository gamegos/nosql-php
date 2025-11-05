<?php
namespace Gamegos\NoSql\Storage;

/* Imports from PHP core */
use UnexpectedValueException;

/**
 * NoSQL Memory Storage
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class Memory extends AbstractStorage
{
    /**
     * Data storage
     * @var array
     */
    private array $data = [];

    /**
     * {@inheritdoc}
     */
    protected function hasInternal(string $key): bool
    {
        if (isset($this->data[$key])) {
            $expiration = $this->data[$key][1];
            if ($expiration <= 0 || time() <= $expiration) {
                return true;
            }
            unset($this->data[$key]);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getInternal(string $key, ?string &$casToken = null): mixed
    {
        if ($this->hasInternal($key)) {
            $value = $this->data[$key][0];
            if (func_num_args() > 1) {
                $casToken            = $this->createCasToken($key, $value);
                $this->data[$key][2] = $casToken;
            }
            return $value;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMultiInternal(array $keys, ?array &$casTokens = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->hasInternal($key)) {
                $result[$key] = $this->data[$key][0];
            }
        }

        if (func_num_args() > 1) {
            $casTokens = [];
            foreach ($result as $key => $value) {
                $casTokens[$key]     = $this->createCasToken($key, $value);
                $this->data[$key][2] = $casTokens[$key];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function addInternal(string $key, mixed $value, int $expiry = 0): bool
    {
        if ($this->hasInternal($key)) {
            return false;
        }
        return $this->setInternal($key, $value, $expiry);
    }

    /**
     * {@inheritdoc}
     */
    protected function setInternal(string $key, mixed $value, int $expiry = 0, ?string $casToken = null): bool
    {
        if ($casToken !== null) {
            return $this->casInternal($casToken, $key, $value, $expiry);
        }
        $expiration       = $expiry <= 0 ? 0 : ($expiry > 2592000 ? $expiry : time() + $expiry);
        $this->data[$key] = [$value, $expiration, null];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function casInternal(string $token, string $key, mixed $value, int $expiry = 0): bool
    {
        if ($token === $this->getCasToken($key)) {
            return $this->setInternal($key, $value, $expiry);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteInternal(string $key): bool
    {
        if ($this->hasInternal($key)) {
            unset($this->data[$key]);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException If existing value is not string
     */
    protected function appendInternal(string $key, string $value, int $expiry = 0): bool
    {
        if ($this->hasInternal($key)) {
            $oldValue = $this->getInternal($key);
            if (!is_string($oldValue)) {
                throw new UnexpectedValueException(sprintf(
                    'Method append() requires existing value to be string, %s found.',
                    gettype($oldValue)
                ));
            }
            $value = $oldValue . $value;
        }
        return $this->setInternal($key, $value, $expiry);
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException If existing value is not integer
     */
    protected function incrementInternal(string $key, int $offset = 1, int $initial = 0, int $expiry = 0): int|bool
    {
        if ($this->hasInternal($key)) {
            $oldValue = $this->getInternal($key);
            if (!is_int($oldValue)) {
                throw new UnexpectedValueException(sprintf(
                    'Method increment() requires existing value to be integer, %s found.',
                    gettype($oldValue)
                ));
            }
            $value = $oldValue + $offset;
        } else {
            $value = $initial;
        }
        // setInternal() always returns true without casToken argument.
        $this->setInternal($key, $value, $expiry);
        return $value;
    }

    /**
     * Get CAS (check-and-set) token for given key.
     * Returns null if the specified key does not exist.
     * @param  string $key
     * @return string|null
     */
    protected function getCasToken(string $key): ?string
    {
        if (isset($this->data[$key])) {
            return $this->data[$key][2];
        }
        return null;
    }

    /**
     * Create a CAS (check-and-set) token for given key-value combination.
     * @param  string $key
     * @param  mixed $value
     * @return string
     */
    protected function createCasToken(string $key, mixed $value): string
    {
        return hash('sha256', serialize([$key, $value]));
    }
}
