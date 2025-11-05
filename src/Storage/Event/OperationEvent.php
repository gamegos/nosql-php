<?php
namespace Gamegos\NoSql\Storage\Event;

use Gamegos\Events\Event;
use Gamegos\NoSql\Storage\StorageInterface;
use Gamegos\NoSql\Storage\OperationArguments;
use Throwable;

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
    protected string $operation;

    /**
     * Arguments of the operation
     * @var \Gamegos\NoSql\Storage\OperationArguments
     */
    protected OperationArguments $arguments;

    /**
     * Return value of the operation
     * @var mixed
     */
    protected mixed $returnValue;

    /**
     * Exception thrown in the operation
     * @var \Throwable
     */
    protected Throwable $exception;

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
     * @param mixed|null $returnValue
     *     Return value of the operation
     * @param \Throwable|null $exception
     *     Exception thrown in the operation
     */
    public function __construct(
        string             $name,
        StorageInterface   $storage,
        string             $operation,
        OperationArguments $arguments,
        mixed              &$returnValue = null,
        ?Throwable          $exception = null
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
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Set operation name.
     * @param string $operation
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * Get operation arguments.
     * @return \Gamegos\NoSql\Storage\OperationArguments
     */
    public function getArguments(): OperationArguments
    {
        return $this->arguments;
    }

    /**
     * Set operation arguments.
     * @param \Gamegos\NoSql\Storage\OperationArguments $arguments
     */
    public function setArguments(OperationArguments $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Get the reference of the returned value.
     * @return mixed
     */
    public function &getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    /**
     * Set the reference of the returned value.
     * @param mixed $returnValue
     */
    public function setReturnValue(mixed &$returnValue): void
    {
        $this->returnValue = & $returnValue;
    }

    /**
     * Get the exception thrown in the operation.
     * @return \Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * Set the exception thrown in the operation.
     * @param \Throwable $exception
     */
    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }
}
