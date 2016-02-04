<?php
namespace Gamegos\NoSql\Storage;

/* Imports from PHP core */
use OutOfRangeException;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\Exception\OperationArgumentException;
use Gamegos\NoSql\Storage\Exception\InvalidKeyException;

/**
 * Argument Container for Storage Operations
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationArguments
{
    /**
     * Operation method
     * @var string
     */
    protected $operation;

    /**
     * Argument values
     * @var array
     */
    protected $values = [];

    /**
     * Construct
     * @param string $operation
     * @param array $args
     */
    public function __construct($operation, array & $args = [])
    {
        $this->operation = $operation;
        foreach ($args as $name => & $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Check if a value is in the specified type.
     * @param  mixed $value
     * @param  string $type
     * @param  bool $nullable
     * @return bool
     * @see    OperationArguments::validateArgument()
     * @see    OperationArguments::validateArrayArgument()
     */
    protected function checkType($value, $type, $nullable = false)
    {
        if (gettype($value) === $type) {
            return true;
        }
        return $nullable && null === $value;
    }

    /**
     * Validate an argument.
     * @param  string $argname
     * @param  mixed $value
     * @param  string $type
     * @param  bool $nullable
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     */
    public function validateArgument($argname, $value, $type, $nullable = false)
    {
        if (!$this->checkType($value, $type, $nullable)) {
            throw new OperationArgumentException(sprintf(
                'Method %s() expects $%s to be %s, %s given.',
                $this->operation,
                $argname,
                $type,
                gettype($value)
            ));
        }
    }

    /**
     * Validate elements in an argument in the type of array.
     * @param  string $argname
     * @param  array $value
     * @param  string $type
     * @param  bool $nullable
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     */
    public function validateArrayArgument($argname, array $value, $type, $nullable = false)
    {
        foreach ($value as $element) {
            if (!$this->checkType($element, $type, $nullable)) {
                throw new OperationArgumentException(sprintf(
                    'Method %s() expects all of $%s elements to be %s, found %s.',
                    $this->operation,
                    $argname,
                    $type,
                    gettype($element)
                ));
            }
        }
    }

    /**
     * Validate a key.
     * @param  mixed $key
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException If the key is not a string
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException If the key is an empty string
     */
    public function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidKeyException('NoSql key is expected to be a string!');
        }
        if ('' === $key) {
            throw new InvalidKeyException('NoSql key is expected to be a non-empty string!');
        }
    }

    /**
     * Check if the specified argument is set.
     * @param  string $argname
     * @return boolean
     */
    public function has($argname)
    {
        return array_key_exists($argname, $this->values);
    }

    /**
     * Get reference of an argument.
     * @param  string $argname
     * @return mixed
     * @throws \OutOfRangeException If the argument is not set
     */
    public function & get($argname)
    {
        if ($this->has($argname)) {
            return $this->values[$argname];
        }
        throw new OutOfRangeException(sprintf('Argument %s does not exist.', $argname));
    }

    /**
     * Set reference an argument.
     * @param  string $argname
     * @param  mixed $value
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function set($argname, & $value)
    {
        $this->values[$argname] = & $value;
        return $this;
    }

    /**
     * Get the reference of the argument 'key'.
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
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setKey(& $key)
    {
        $this->validateKey($key);
        return $this->set('key', $key);
    }

    /**
     * Get the reference of the argument 'casToken'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *
     * @throws \OutOfRangeException If the argument is not set
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
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *
     * @param  string $casToken
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setCasToken(& $casToken)
    {
        $this->validateArgument('casToken', $casToken, 'string', true);
        return $this->set('casToken', $casToken);
    }

    /**
     * Get the reference of the argument 'keys'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setKeys(array & $keys)
    {
        array_walk($keys, [$this, 'validateKey']);
        return $this->set('keys', $keys);
    }

    /**
     * Get the reference of the argument 'casTokens'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::getMulti()}
     *
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setCasTokens(array & $casTokens)
    {
        $this->validateArrayArgument('casTokens', $casTokens, 'string', true);
        return $this->set('casTokens', $casTokens);
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
     * @throws \OutOfRangeException If the argument is not set
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
        if ('append' == $this->operation) {
            $this->validateArgument('value', $value, 'string');
        }
        return $this->set('value', $value);
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
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setExpiry(& $expiry)
    {
        $this->validateArgument('expiry', $expiry, 'integer');
        return $this->set('expiry', $expiry);
    }

    /**
     * Get the reference of the argument 'offset'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setOffset(& $offset)
    {
        $this->validateArgument('offset', $offset, 'integer');
        return $this->set('offset', $offset);
    }

    /**
     * Get the reference of the argument 'initial'.
     *
     * Methods using this argument:
     *   {@link AbstractStorage::increment()}
     *
     * @throws \OutOfRangeException If the argument is not set
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
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setInitial(& $initial)
    {
        $this->validateArgument('initial', $initial, 'integer');
        return $this->set('initial', $initial);
    }
}
