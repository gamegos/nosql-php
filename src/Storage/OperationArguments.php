<?php
namespace Gamegos\NoSql\Storage;

use Gamegos\NoSql\Storage\Exception\OperationArgumentException;
use Gamegos\NoSql\Storage\Exception\InvalidKeyException;
use OutOfRangeException;

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
    protected string $operation;

    /**
     * Argument values
     * @var array
     */
    protected array $values = [];

    /**
     * Construct
     * @param string $operation
     * @param array $args
     */
    public function __construct(string $operation, array &$args = [])
    {
        $this->operation = $operation;
        foreach ($args as $name => &$value) {
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
    protected function checkType(mixed $value, string $type, bool $nullable = false): bool
    {
        if (gettype($value) === $type) {
            return true;
        }
        return $nullable && null === $value;
    }

    /**
     * Validate an argument.
     * @param  string $argName
     * @param  mixed $value
     * @param  string $type
     * @param  bool $nullable
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     */
    public function validateArgument(string $argName, mixed $value, string $type, bool $nullable = false): void
    {
        if (!$this->checkType($value, $type, $nullable)) {
            throw new OperationArgumentException(sprintf(
                'Method %s() expects $%s to be %s, %s given.',
                $this->operation,
                $argName,
                $type,
                gettype($value)
            ));
        }
    }

    /**
     * Validate elements in an argument in the type of array.
     * @param  string $argName
     * @param  array $value
     * @param  string $type
     * @param  bool $nullable
     * @throws \Gamegos\NoSql\Storage\Exception\OperationArgumentException
     */
    public function validateArrayArgument(string $argName, array $value, string $type, bool $nullable = false): void
    {
        foreach ($value as $element) {
            if (!$this->checkType($element, $type, $nullable)) {
                throw new OperationArgumentException(sprintf(
                    'Method %s() expects all of $%s elements to be %s, found %s.',
                    $this->operation,
                    $argName,
                    $type,
                    gettype($element)
                ));
            }
        }
    }

    /**
     * Validate a key.
     * @param  string $key
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException If the key is not a string
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException If the key is an empty string
     */
    public function validateKey(string $key): void
    {
        if ('' == $key) {
            throw new InvalidKeyException('NoSql key is expected to be a non-empty string.');
        }
    }

    /**
     * Check if the specified argument is set.
     * @param  string $argName
     * @return boolean
     */
    public function has(string $argName): bool
    {
        return array_key_exists($argName, $this->values);
    }

    /**
     * Get reference of an argument.
     * @param  string $argName
     * @return mixed
     * @throws \OutOfRangeException If the argument is not set
     */
    public function &get(string $argName): mixed
    {
        if ($this->has($argName)) {
            return $this->values[$argName];
        }
        throw new OutOfRangeException(sprintf('Argument %s does not exist.', $argName));
    }

    /**
     * Set reference an argument.
     * @param  string $argName
     * @param  mixed $value
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function set(string $argName, mixed &$value): static
    {
        $this->values[$argName] = &$value;
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
    public function &getKey(): string
    {
        return $this->get('key');
    }

    /**
     * Set the reference of the argument 'key'.
     * Methods using this argument:
     *   {@link AbstractStorage::has()}
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::add()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     *   {@link AbstractStorage::delete()}
     *   {@link AbstractStorage::append()}
     *   {@link AbstractStorage::increment()}
     * @param  string $key
     * @return \Gamegos\NoSql\Storage\OperationArguments
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function setKey(string &$key): static
    {
        $this->validateKey($key);
        return $this->set('key', $key);
    }

    /**
     * Get the reference of the argument 'casToken'.
     * Methods using this argument:
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     * @return string|null
     */
    public function &getCasToken(): ?string
    {
        return $this->get('casToken');
    }

    /**
     * Set the reference of the argument 'casToken'.
     * Methods using this argument:
     *   {@link AbstractStorage::get()}
     *   {@link AbstractStorage::set()}
     *   {@link AbstractStorage::cas()}
     * @param  string|null $casToken
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function setCasToken(?string &$casToken): static
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
    public function &getKeys(): array
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
    public function setKeys(array &$keys): static
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
    public function &getCasTokens(): array
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
    public function setCasTokens(array &$casTokens): static
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
    public function &getValue(): mixed
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
    public function setValue(mixed &$value): static
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
    public function &getExpiry(): int
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
    public function setExpiry(int &$expiry): static
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
    public function &getOffset(): int
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
    public function setOffset(int &$offset): static
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
    public function &getInitial(): int
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
    public function setInitial(int &$initial): static
    {
        $this->validateArgument('initial', $initial, 'integer');
        return $this->set('initial', $initial);
    }
}
