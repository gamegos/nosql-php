<?php
namespace Gamegos\NoSql\Storage;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\Exception\OperationArgumentException;

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
    private $data = [];

    /**
     * {@inheritdoc}
     */
    protected function hasInternal($key)
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
    protected function getInternal($key, & $casToken = null)
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
    protected function getMultiInternal(array $keys, array & $casTokens = null)
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
    protected function addInternal($key, $value, $expiry = 0)
    {
        if ($this->hasInternal($key)) {
            return false;
        }
        return $this->setInternal($key, $value, $expiry);
    }

    /**
     * {@inheritdoc}
     */
    protected function setInternal($key, $value, $expiry = 0, $casToken = null)
    {
        if (func_num_args() > 3) {
            return $this->casInternal($casToken, $key, $value, $expiry);
        }
        $expiration       = $expiry <= 0 ? 0 : ($expiry > 2592000 ? $expiry : time() + $expiry);
        $this->data[$key] = [$value, $expiration, null];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function casInternal($casToken, $key, $value, $expiry = 0)
    {
        if ($casToken === $this->getCasToken($key)) {
            return $this->setInternal($key, $value, $expiry);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteInternal($key)
    {
        if ($this->hasInternal($key)) {
            unset($this->data[$key]);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException If $value is not string
     * @throws \UnexpectedValueException                                   If existing value is not string
     */
    protected function appendInternal($key, $value, $expiry = 0)
    {
        if (!is_string($value)) {
            throw new OperationArgumentException(sprintf(
                'Method append() expects $value to be string, %s given.',
                gettype($value)
            ));
        }

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
    protected function incrementInternal($key, $offset = 1, $initial = 0, $expiry = 0)
    {
        if ($this->hasInternal($key)) {
            $oldValue = $this->getInternal($key);
            if (!is_int($oldValue)) {
                throw new UnexpectedValueException(sprintf(
                    'Method increment() requires existing value to be integer, %s found.',
                    gettype($oldValue)
                ));
            }
            $value = $oldValue + (int) $offset;
        } else {
            $value = (int) $initial;
        }
        return $this->setInternal($key, $value, $expiry);
    }

    /**
     * Get CAS (check-and-set) token for given key.
     * Returns null if the specified key does not exist.
     * @param  string $key
     * @return string|null
     */
    protected function getCasToken($key)
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
    protected function createCasToken($key, $value)
    {
        return uniqid(md5(serialize([$key, $value])));
    }
}
