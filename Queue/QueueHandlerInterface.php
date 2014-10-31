<?php

namespace Crocos\QueueBundle\Queue;

/**
 * QueueHandlerInterface.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
interface QueueHandlerInterface
{
    /**
     * Handle queue message.
     *
     * @param string $message
     */
    public function handleMessage($message);
}
