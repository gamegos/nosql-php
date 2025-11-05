<?php
namespace Gamegos\NoSql\Storage;

use Gamegos\Events\EventManager;
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\Event\OperationListenerInterface;
use Throwable;

/**
 * Base Class for NoSql Storages
 * @author Safak Ozpinar <safak@gamegos.com>
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * Event manager
     * @var \Gamegos\Events\EventManager
     */
    protected EventManager $eventManager;

    /**
     * Get operation event manager.
     * @return \Gamegos\Events\EventManager
     */
    protected function getEventManager(): EventManager
    {
        if (!isset($this->eventManager)) {
            $this->eventManager = new EventManager();
        }
        return $this->eventManager;
    }

    /**
     * Trigger 'beforeOperation' event.
     * @param  string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     */
    protected function fireBeforeOperation(string $operation, OperationArguments $args): void
    {
        $this->getEventManager()->triggerEvent(
            new OperationEvent('beforeOperation', $this, $operation, $args)
        );
    }

    /**
     * Trigger 'afterOperation' event.
     * @param  string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     * @param mixed $returnValue
     */
    protected function fireAfterOperation(string $operation, OperationArguments $args, mixed &$returnValue): void
    {
        $this->getEventManager()->triggerEvent(
            new OperationEvent('afterOperation', $this, $operation, $args, $returnValue)
        );
    }

    /**
     * Trigger 'onOperationException' event.
     * @param  string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     * @param \Throwable $exception
     */
    protected function fireOnOperationException(string $operation, OperationArguments $args, Throwable $exception): void
    {
        $returnValue = null;
        $this->getEventManager()->triggerEvent(
            new OperationEvent('onOperationException', $this, $operation, $args, $returnValue, $exception)
        );
    }

    /**
     * Execute operation with event handling and argument preparation.
     * @param  string $operation
     * @param  callable $executor
     * @param  OperationArguments $args
     * @return mixed
     * @throws \Throwable
     */
    protected function executeOperation(string $operation, callable $executor, OperationArguments $args): mixed
    {
        try {
            // Trigger 'beforeOperation' event.
            $this->fireBeforeOperation($operation, $args);

            // Execute the operation
            $returnValue = $executor($args);

            // Trigger 'afterOperation' event.
            $this->fireAfterOperation($operation, $args, $returnValue);

            return $returnValue;
        } catch (Throwable $e) {
            // Trigger 'onOperationException' event.
            $this->fireOnOperationException($operation, $args, $e);
            throw $e;
        }
    }

    /**
     * Attach a listener for operation events.
     * @param \Gamegos\NoSql\Storage\Event\OperationListenerInterface $listener
     * @param  int $priority
     */
    public function addOperationListener(OperationListenerInterface $listener, int $priority = 0): void
    {
        $this->getEventManager()->attach('beforeOperation', [$listener, 'beforeOperation'], $priority);
        $this->getEventManager()->attach('afterOperation', [$listener, 'afterOperation'], $priority);
        $this->getEventManager()->attach('onOperationException', [$listener, 'onOperationException'], $priority);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function has(string $key): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key);
        return $this->executeOperation(__FUNCTION__, fn(OperationArguments $args) => $this->hasInternal($args->getKey()), $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function get(string $key, ?string &$casToken = null): mixed
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key);
        if (func_num_args() > 1) {
            $args->setCasToken($casToken);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            if ($args->has('casToken')) {
                return $this->getInternal($args->getKey(), $args->getCasToken());
            }
            return $this->getInternal($args->getKey());
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function getMulti(array $keys, ?array &$casTokens = null): array
    {
        $args = (new OperationArguments(__FUNCTION__))->setKeys($keys);
        if (func_num_args() > 1) {
            $args->setCasTokens($casTokens);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            if ($args->has('casTokens')) {
                return $this->getMultiInternal($args->getKeys(), $args->getCasTokens());
            }
            return $this->getMultiInternal($args->getKeys());
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function add(string $key, mixed $value, int $expiry = 0): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $args->setExpiry($expiry);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            $arguments = [$args->getKey(), $args->getValue()];
            if ($args->has('expiry')) {
                $arguments[] = $args->getExpiry();
            }
            return $this->addInternal(...$arguments);
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function set(string $key, mixed $value, int $expiry = 0, ?string $casToken = null): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $args->setExpiry($expiry);
        }
        if (func_num_args() > 3) {
            $args->setCasToken($casToken);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            $arguments = [$args->getKey(), $args->getValue()];
            if ($args->has('expiry')) {
                $arguments[] = $args->getExpiry();
            }
            if ($args->has('casToken')) {
                $arguments[] = $args->getCasToken();
            }
            return $this->setInternal(...$arguments);
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function cas(string $casToken, string $key, mixed $value, int $expiry = 0): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setCasToken($casToken)->setKey($key)->setValue($value);
        if (func_num_args() > 3) {
            $args->setExpiry($expiry);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            if ($args->has('expiry')) {
                return $this->casInternal($args->getCasToken(), $args->getKey(), $args->getValue(), $args->getExpiry());
            }
            return $this->casInternal($args->getCasToken(), $args->getKey(), $args->getValue());
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function delete(string $key): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key);
        return $this->executeOperation(__FUNCTION__, fn(OperationArguments $args) => $this->deleteInternal($args->getKey()), $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function append(string $key, string $value, int $expiry = 0): bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $args->setExpiry($expiry);
        }
        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            if ($args->has('expiry')) {
                return $this->appendInternal($args->getKey(), $args->getValue(), $args->getExpiry());
            }
            return $this->appendInternal($args->getKey(), $args->getValue());
        }, $args);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     * @throws \Throwable
     */
    public function increment(string $key, int $offset = 1, int $initial = 0, int $expiry = 0): int|bool
    {
        $args = (new OperationArguments(__FUNCTION__))->setKey($key);
        if (func_num_args() > 1) {
            $args->setOffset($offset);
        }
        if (func_num_args() > 2) {
            $args->setInitial($initial);
        }
        if (func_num_args() > 3) {
            $args->setExpiry($expiry);
        }

        return $this->executeOperation(__FUNCTION__, function(OperationArguments $args) {
            $arguments = [$args->getKey()];
            if ($args->has('offset')) {
                $arguments[] = $args->getOffset();
            }
            if ($args->has('initial')) {
                $arguments[] = $args->getInitial();
            }
            if ($args->has('expiry')) {
                $arguments[] = $args->getExpiry();
            }
            return $this->incrementInternal(...$arguments);
        }, $args);
    }

    /**
     * Check if a key exists in the storage.
     * @param  string $key
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::has()
     */
    abstract protected function hasInternal(string $key): bool;

    /**
     * Get a value from the storage.
     * @param  string $key
     * @param  string|null $casToken
     * @return mixed
     * @see    \Gamegos\NoSql\Storage\StorageInterface::get()
     */
    abstract protected function getInternal(string $key, ?string &$casToken = null): mixed;

    /**
     * Get multiple values from the storage.
     * @param  array $keys
     * @param  array|null $casTokens
     * @return array
     * @see    \Gamegos\NoSql\Storage\StorageInterface::getMulti()
     */
    abstract protected function getMultiInternal(array $keys, ?array &$casTokens = null): array;

    /**
     * Add a value under a new key.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::add()
     */
    abstract protected function addInternal(string $key, mixed $value, int $expiry = 0): bool;

    /**
     * Store/update a value in the storage.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @param  string|null $casToken
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::set()
     */
    abstract protected function setInternal(string $key, mixed $value, int $expiry = 0, ?string $casToken = null): bool;

    /**
     * Store a value only if the token matches.
     * @param  string $token
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::cas()
     */
    abstract protected function casInternal(string $token, string $key, mixed $value, int $expiry = 0): bool;

    /**
     * Delete a value from the storage.
     * @param  string $key
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::delete()
     */
    abstract protected function deleteInternal(string $key): bool;

    /**
     * Append a value to an existing value.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::append()
     */
    abstract protected function appendInternal(string $key, string $value, int $expiry = 0): bool;

    /**
     * Increment value of a numeric entry.
     * @param  string $key
     * @param  int $offset
     * @param  int $initial
     * @param  int $expiry
     * @return int|boolean
     * @see    \Gamegos\NoSql\Storage\StorageInterface::increment()
     */
    abstract protected function incrementInternal(string $key, int $offset = 1, int $initial = 0, int $expiry = 0): int|bool;
}
