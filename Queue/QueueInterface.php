<?php

namespace Crocos\QueueBundle\Queue;

/**
 * QueueInterface.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
interface QueueInterface
{
    /**
     * Get queue name.
     *
     * @return string
     */
    public function getName();

    /**
     * Enqueue message to a queue.
     *
     * @param mixed $message
     * @param integer $priority
     */
    public function enqueue($message, $priority = 10);

    /**
     * Dequeue message from top of a queue.
     *
     * @return string Message
     */
    public function dequeue();

    /**
     * Re connect.
     */
    public function reconnect();
}
