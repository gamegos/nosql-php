<?php
namespace Gamegos\NoSql\Storage;

/* Imports from PHP core */
use Exception;

/* Imports from gamegos/events */
use Gamegos\Events\EventManager;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\Exception\InvalidKeyException;
use Gamegos\NoSql\Storage\Event\OperationEvent;
use Gamegos\NoSql\Storage\Event\OperationListenerInterface;

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
    protected $eventManager;

    /**
     * Get operation event manager.
     * @return \Gamegos\Events\EventManager
     */
    protected function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager();
        }
        return $this->eventManager;
    }

    /**
     * Trigger 'beforeOperation' event.
     * @param string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     */
    protected function fireBeforeOperation($operation, OperationArguments $args)
    {
        $this->getEventManager()->triggerEvent(
            new OperationEvent('beforeOperation', $this, $operation, $args)
        );
    }

    /**
     * Trigger 'afterOperation' event.
     * @param string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     */
    protected function fireAfterOperation($operation, OperationArguments $args, & $returnValue)
    {
        $this->getEventManager()->triggerEvent(
            new OperationEvent('afterOperation', $this, $operation, $args, $returnValue)
        );
    }

    /**
     * Trigger 'onOperationException' event.
     * @param string $operation
     * @param \Gamegos\NoSql\Storage\OperationArguments $args
     */
    protected function fireOnOperationException($operation, OperationArguments $args, Exception $exception)
    {
        $returnValue = null;
        $this->getEventManager()->triggerEvent(
            new OperationEvent('onOperationException', $this, $operation, $args, $returnValue, $exception)
        );
    }

    /**
     * Do the specified operation.
     * @param  string $operation
     * @param  \Gamegos\NoSql\Storage\OperationArguments $args
     * @throws \Exception
     */
    protected function doOperation($operation, OperationArguments $args)
    {
        try {
            // Trigger 'beforeOperation' event.
            $this->fireBeforeOperation($operation, $args);
            // Run the internal operation.
            $forwardMethod = 'forward' . ucfirst($operation);
            $returnValue   = call_user_func([$this, $forwardMethod], $args);
            // Trigger 'afterOperation' event.
            $this->fireAfterOperation($operation, $args, $returnValue);
            // Return the operation result.
            return $returnValue;
        } catch (Exception $e) {
            // Trigger 'onOperationException' event.
            $this->fireOnOperationException($operation, $args, $e);
            // Throw the original exception.
            throw $e;
        }
    }

    /**
     * Forward has() method params to hasInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardHas(OperationArguments $args)
    {
        return $this->hasInternal($args->getKey());
    }

    /**
     * Forward get() method params to getInternal() method.
     * @param  OperationArguments $args
     * @return mixed
     */
    private function forwardGet(OperationArguments $args)
    {
        if ($args->has('casToken')) {
            return $this->getInternal($args->getKey(), $args->getCasToken());
        }
        return $this->getInternal($args->getKey());
    }

    /**
     * Forward getMulti() method params to getMultiInternal() method.
     * @param  OperationArguments $args
     * @return array
     */
    private function forwardGetMulti(OperationArguments $args)
    {
        if ($args->has('casTokens')) {
            return $this->getMultiInternal($args->getKeys(), $args->getCasTokens());
        }
        return $this->getMultiInternal($args->getKeys());
    }

    /**
     * Forward add() method params to addInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardAdd(OperationArguments $args)
    {
        if ($args->has('expiry')) {
            return $this->addInternal($args->getKey(), $args->getValue(), $args->getExpiry());
        }
        return $this->addInternal($args->getKey(), $args->getValue());
    }

    /**
     * Forward set() method params to setInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardSet(OperationArguments $args)
    {
        if ($args->has('expiry')) {
            if ($args->has('casToken')) {
                return $this->setInternal($args->getKey(), $args->getValue(), $args->getExpiry(), $args->getCasToken());
            }
            return $this->setInternal($args->getKey(), $args->getValue(), $args->getExpiry());
        }
        return $this->setInternal($args->getKey(), $args->getValue());
    }

    /**
     * Forward cas() method params to casInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardCas(OperationArguments $args)
    {
        if ($args->has('expiry')) {
            return $this->casInternal($args->getCasToken(), $args->getKey(), $args->getValue(), $args->getExpiry());
        }
        return $this->casInternal($args->getCasToken(), $args->getKey(), $args->getValue());
    }

    /**
     * Forward delete() method params to deleteInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardDelete(OperationArguments $args)
    {
        return $this->deleteInternal($args->getKey());
    }

    /**
     * Forward append() method params to appendInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardAppend(OperationArguments $args)
    {
        if ($args->has('expiry')) {
            return $this->appendInternal($args->getKey(), $args->getValue(), $args->getExpiry());
        }
        return $this->appendInternal($args->getKey(), $args->getValue());
    }

    /**
     * Forward increment() method params to incrementInternal() method.
     * @param  OperationArguments $args
     * @return bool
     */
    private function forwardIncrement(OperationArguments $args)
    {
        if ($args->has('offset')) {
            if ($args->has('initial')) {
                if ($args->has('expiry')) {
                    return $this->incrementInternal(
                        $args->getKey(),
                        $args->getOffset(),
                        $args->getInitial(),
                        $args->getExpiry()
                    );
                }
                return $this->incrementInternal($args->getKey(), $args->getOffset(), $args->getInitial());
            }
            return $this->incrementInternal($args->getKey(), $args->getOffset());
        }
        return $this->incrementInternal($args->getKey());
    }

    /**
     * Attach a listener for operation events.
     * @param \Gamegos\NoSql\Storage\Event\OperationListenerInterface $listener
     * @param int $priority
     */
    public function addOperationListener(OperationListenerInterface $listener, $priority = 0)
    {
        $this->getEventManager()->attach('beforeOperation', [$listener, 'beforeOperation'], $priority);
        $this->getEventManager()->attach('afterOperation', [$listener, 'afterOperation'], $priority);
        $this->getEventManager()->attach('onOperationException', [$listener, 'onOperationException'], $priority);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function has($key)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key);
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function get($key, & $casToken = null)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key);
        if (func_num_args() > 1) {
            $arguments->setCasToken($casToken);
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function getMulti(array $keys, array & $casTokens = null)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKeys($keys);
        if (func_num_args() > 1) {
            $arguments->setCasTokens($casTokens);
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function add($key, $value, $expiry = 0)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $arguments->setExpiry($expiry);
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function set($key, $value, $expiry = 0, $casToken = null)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $arguments->setExpiry($expiry);
            if (func_num_args() > 3) {
                $arguments->setCasToken($casToken);
            }
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function cas($casToken, $key, $value, $expiry = 0)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setCasToken($casToken)->setKey($key)->setValue($value);
        if (func_num_args() > 3) {
            $arguments->setExpiry($expiry);
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function delete($key)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key);
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function append($key, $value, $expiry = 0)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key)->setValue($value);
        if (func_num_args() > 2) {
            $arguments->setExpiry($expiry);
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * {@inheritdoc}
     * @throws \Gamegos\NoSql\Storage\Exception\InvalidKeyException
     */
    public function increment($key, $offset = 1, $initial = 0, $expiry = 0)
    {
        $operation = __FUNCTION__;
        $arguments = (new OperationArguments($operation))->setKey($key);
        if (func_num_args() > 1) {
            $arguments->setOffset($offset);
            if (func_num_args() > 2) {
                $arguments->setInitial($initial);
                if (func_num_args() > 3) {
                    $arguments->setExpiry($expiry);
                }
            }
        }
        return $this->doOperation($operation, $arguments);
    }

    /**
     * Check if a key exists in the storage.
     * @param  string $key
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::has()
     */
    abstract protected function hasInternal($key);

    /**
     * Get a value from the storage.
     * @param  string $key
     * @param  string $casToken
     * @return mixed
     * @see    \Gamegos\NoSql\Storage\StorageInterface::get()
     */
    abstract protected function getInternal($key, & $casToken = null);

    /**
     * Get multiple values from the storage.
     * @param  array $keys
     * @param  array $casTokens
     * @return array
     * @see    \Gamegos\NoSql\Storage\StorageInterface::getMulti()
     */
    abstract protected function getMultiInternal(array $keys, array & $casTokens = null);

    /**
     * Add an value under a new key.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::add()
     */
    abstract protected function addInternal($key, $value, $expiry = 0);

    /**
     * Store/update a value in the storage.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @param  string $casToken
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::set()
     */
    abstract protected function setInternal($key, $value, $expiry = 0, $casToken = null);

    /**
     * Store a value only if the token matches.
     * @param  string $token
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::cas()
     */
    abstract protected function casInternal($token, $key, $value, $expiry = 0);

    /**
     * Delete a value from the storage.
     * @param  string $key
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::delete()
     */
    abstract protected function deleteInternal($key);

    /**
     * Append a value to an existing value.
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiry
     * @return bool
     * @see    \Gamegos\NoSql\Storage\StorageInterface::append()
     */
    abstract protected function appendInternal($key, $value, $expiry = 0);

    /**
     * Increment value of a numeric entry.
     * @param  string $key
     * @param  int $offset
     * @param  int $initial
     * @param  int $expiry
     * @return int|boolean
     * @see    \Gamegos\NoSql\Storage\StorageInterface::increment()
     */
    abstract protected function incrementInternal($key, $offset = 1, $initial = 0, $expiry = 0);
}
