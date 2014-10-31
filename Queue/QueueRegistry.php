<?php

namespace Crocos\QueueBundle\Queue;

/**
 * QueueRegistry.
 *
 * @aurhor Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class QueueRegistry
{
    /**
     * @var \SplObjectStorage
     */
    private $queues;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->queues = new \SplObjectStorage();
    }

    /**
     * Register queue.
     *
     * @param QueueInterface $queue
     */
    public function registerQueue(QueueInterface $queue)
    {
        $this->queues[$queue] = array();
    }

    /**
     * Register queue handler.
     *
     * @param QueueInterface $queue
     * @param QueueHandlerInterface $handler
     */
    public function registerQueueHandler(QueueInterface $queue, QueueHandlerInterface $handler)
    {
        if (!isset($this->queues[$queue])) {
            $this->registerQueue($queue);
        }

        $handlers = $this->queues[$queue];

        $handlers[] = $handler;

        $this->queues[$queue] = $handlers;
    }

    /**
     * Get queues.
     *
     * @return \SplObjectStorage
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * Get queue handler.
     *
     * @return array
     */
    public function getQueueHandlers(QueueInterface $queue)
    {
        return $this->queues[$queue];
    }
}
