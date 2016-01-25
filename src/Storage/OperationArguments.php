<?php
namespace Gamegos\NoSql\Storage;

/* Imports from PHP core */
use InvalidArgumentException;

/**
 * Argument Container for Storage Operations
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationArguments
{
    /**
     * Argument values
     * @var array
     */
    protected $values = [];

    /**
     * Check if the specified argument is set.
     * @param  string $argument
     * @return boolean
     */
    public function has($argument)
    {
        return array_key_exists($argument, $this->values);
    }

    /**
     * Get reference of an argument.
     * @param  string $argument
     * @return mixed
     * @throws \InvalidArgumentException if the argument is not set.
     */
    public function & get($argument)
    {
        if ($this->has($argument)) {
            return $this->values[$argument];
        }
        throw new InvalidArgumentException("Argument {$argument} does not exist.");
    }

    /**
     * Set reference an argument.
     * @param  string $argument
     * @param  mixed $value
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function set($argument, & $value)
    {
        $this->values[$argument] = & $value;
        return $this;
    }

    /**
     * Get the reference of the argument 'key'.
     * Methods using this argument:
     *   has, get, add, set, cas, delete, append, increment
     * @return string
     */
    public function & getKey()
    {
        return $this->get('key');
    }

    /**
     * Set the reference of the argument 'key'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::has()}
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::delete()}
     *   {@link AbstractStorage::append()}
     *   {@link AbstractStorage::increment()}
     *
     * @param  string $key
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setKey(& $key)
    {
        $this->values['key'] = & $key;
        return $this;
    }

    /**
     * Get the reference of the argument 'casToken'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::cas()}
     *
     * @return string
     */
    public function & getCasToken()
    {
        return $this->get('casToken');
    }

    /**
     * Set the reference of the argument 'casToken'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::cas()}
     *
     * @param  string $casToken
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setCasToken(& $casToken)
    {
        $this->values['casToken'] = & $casToken;
        return $this;
    }

    /**
     * Get the reference of the argument 'keys'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @return array
     */
    public function & getKeys()
    {
        return $this->get('keys');
    }

    /**
     * Set the reference of the argument 'keys'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @param  array $keys
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setKeys(array & $keys)
    {
        $this->values['keys'] = & $keys;
        return $this;
    }

    /**
     * Get the reference of the argument 'casTokens'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @return array
     */
    public function & getCasTokens()
    {
        return $this->get('casTokens');
    }

    /**
     * Set the reference of the argument 'casTokens'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @param  array $casTokens
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setCasTokens(array & $casTokens)
    {
        $this->values['casTokens'] = & $casTokens;
        return $this;
    }

    /**
     * Get the reference of the argument 'value'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::append()}
     *
     * @return mixed
     */
    public function & getValue()
    {
        return $this->get('value');
    }

    /**
     * Set the reference of the argument 'value'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::append()}
     *
     * @param  mixed $value
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setValue(& $value)
    {
        $this->values['value'] = & $value;
        return $this;
    }

    /**
     * Get the reference of the argument 'expiry'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::append()}
     *   {@link AbstractStorage::increment()}
     *
     * @return int
     */
    public function & getExpiry()
    {
        return $this->get('expiry');
    }

    /**
     * Set the reference of the argument 'expiry'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::append()}
     *   {@link AbstractStorage::increment()}
     *
     * @param  int $expiry
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setExpiry(& $expiry)
    {
        $this->values['expiry'] = & $expiry;
        return $this;
    }

    /**
     * Get the reference of the argument 'offset'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @return int
     */
    public function & getOffset()
    {
        return $this->get('offset');
    }

    /**
     * Set the reference of the argument 'offset'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @param  int $offset
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setOffset(& $offset)
    {
        $this->values['offset'] = & $offset;
        return $this;
    }

    /**
     * Get the reference of the argument 'initial'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @return int
     */
    public function & getInitial()
    {
        return $this->get('initial');
    }

    /**
     * Set the reference of the argument 'initial'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @param  int $initial
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setInitial(& $initial)
    {
        $this->values['initial'] = & $initial;
        return $this;
    }
}
