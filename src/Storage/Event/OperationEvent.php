<?php
namespace Gamegos\NoSql\Storage\Event;

/* Imports from gamegos/events */
use Gamegos\Events\Event;

/* Imports from gamegos/nosql */
use Gamegos\NoSql\Storage\StorageInterface;
use Gamegos\NoSql\Storage\OperationArguments;

/* Imports from PHP core */
use Exception;

/**
 * Operation Event
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class OperationEvent extends Event
{
    /**
     * Operation name
     * @var string
     */
    protected $operation;

    /**
     * Arguments of the operation
     * @var \Gamegos\NoSql\Storage\OperationArguments
     */
    protected $arguments;

    /**
     * Return value of the operation
     * @var mixed
     */
    protected $returnValue;

    /**
     * Exception thrown in the operation
     * @var \Exception
     */
    protected $exception;

    /**
     * Construct
     * @param string $name
     *     Event name
     * @param \Gamegos\NoSql\Storage\StorageInterface $storage
     *     Target storage
     * @param string $operation
     *     Operation name
     * @param \Gamegos\NoSql\Storage\OperationArguments $arguments
     *     Operation arguments
     * @param mixed $returnValue
     *     Return value of the operation
     * @param \Exception $exception
     *     Exception thrown in the operation
     */
    public function __construct(
        $name,
        StorageInterface $storage,
        $operation,
        OperationArguments $arguments,
        & $returnValue = null,
        Exception $exception = null
    ) {
        parent::__construct($name, $storage);
        $this->setOperation($operation);
        $this->setArguments($arguments);
        if (null !== $returnValue) {
            $this->setReturnValue($returnValue);
        }
        if (null !== $exception) {
            $this->setException($exception);
        }
    }

    /**
     * Get operation name.
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set operation name.
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = (string) $operation;
    }

    /**
     * Get operation arguments.
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set operation arguments.
     * @param \Gamegos\NoSql\Storage\OperationArguments $arguments
     */
    public function setArguments(OperationArguments $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Get the reference of the returned value.
     * @return mixed
     */
    public function & getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * Set the reference of the returned value.
     * @param mixed $returnValue
     */
    public function setReturnValue(& $returnValue)
    {
        $this->returnValue = & $returnValue;
    }

    /**
     * Get the exception thrown in the operation.
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set the exception thrown in the operation.
     * @param \Exception $exception
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
    }
}
