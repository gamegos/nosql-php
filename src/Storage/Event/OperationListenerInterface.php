<?php
namespace Gamegos\NoSql\Storage\Event;

/**
 * Listener Interface for Operation Events
 * @author Safak Ozpinar <safak@gamegos.com>
 */
interface OperationListenerInterface
{
    /**
     * Handle 'beforeOperation' event.
     * @param \Gamegos\NoSql\Storage\Event\OperationEvent $e
     */
    public function beforeOperation(OperationEvent $e);

    /**
     * Handle 'afterOperation' event.
     * @param \Gamegos\NoSql\Storage\Event\OperationEvent $e
     */
    public function afterOperation(OperationEvent $e);

    /**
     * Handle 'onOperationException' event.
     * @param \Gamegos\NoSql\Storage\Event\OperationEvent $e
     */
    public function onOperationException(OperationEvent $e);
}
