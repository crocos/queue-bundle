<?php

namespace Crocos\QueueBundle\Worker;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Crocos\QueueBundle\Exception\QueueException;
use Crocos\QueueBundle\Queue\QueueInterface;
use Crocos\QueueBundle\Queue\QueueRegistry;

/**
 * Queue Worker.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class Worker
{
    protected $registry;
    protected $logger;

    protected $start;
    protected $shutdown = false;
    protected $uinterval;

    /**
     * Constructor.
     *
     * @param QueueRegistry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(QueueRegistry $registry, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger = $logger;

    }

    /**
     * Work queue.
     *
     * @param integer $interval
     */
    public function work($interval = 1000)
    {
        $this->boot($interval);

        $i = $j = 0;

        $queues = $this->registry->getQueues();

        do {
            try {
                $handled = false;
                foreach ($queues as $queue) {
                    if ($handled) {
                        $queue->reconnect();
                    }

                    $lastMessage = null;
                    $lastHandled = null;
                    try {
                        // handle each message
                        if (null !== ($message = $queue->top())) {
                            $lastMessage = $message;
                            $lastHandled = false;

                            $this->handleMessage($queue, $message);

                            $handled = true;
                            $lastHandled = true;
                        }
                    } catch (\Exception $e) {
                        if (null !== $this->logger) {
                            $this->logger->err(sprintf('Queue error on handle message (queue="%s", message="%s", handled=%s)'
                                , $queue->getName()
                                , $lastMessage
                                , $lastHandled ? 'true' : 'false'
                            ));
                        }

                        throw $e;
                    }
                }

                if ($handled) {
                    foreach ($queues as $queue) {
                        $queue->reconnect();
                    }
                }

                usleep($this->uinterval);

                // statistics
                if (0 === ++$i % 10000 && null !== $this->logger) {
                    $this->logger->debug(sprintf('[stats] memory usage: %sMB (in %d0000th loop)', number_format(memory_get_usage(true) / 1000000, 2), ++$j));
                    $this->logger->debug(sprintf('[stats] uptime: %s', $this->start->diff(new \DateTime())->format('%a days %H:%I')));
                    $i = 0;
                }
            } catch (\Exception $e) {
                $this->shutdown = true;

                if (null !== $this->logger) {
                    $this->logger->err(sprintf('Queue error: [%s] %s (shutdown=%s)'
                        , get_class($e)
                        , $e->getMessage()
                        , $this->shutdown ? 'true' : 'false'
                    ));
                }
            }
        } while (false === $this->shutdown);

        return $this->shutdown();
    }

    /**
     * @see doHandleMessage()
     */
    protected function handleMessage(QueueInterface $queue, $message)
    {
        $pid = pcntl_fork();

        if (-1 == $pid) {
            if (null !== $this->logger) {
                $this->logger->error('Process fork failed. This queue will be shut down');
            }

            $this->shutdown = true;
        } elseif ($pid) {
            // wait queue handling
            pcntl_waitpid($pid, $status);
        } else {
            // handle queue
            $this->doHandleMessage($queue, $message);

            exit(0);
        }
    }

    /**
     * Handler queue message.
     *
     * @param QueueInterface $queue
     * @param string $message
     */
    protected function doHandleMessage(QueueInterface $queue, $message)
    {
        $handlers = $this->registry->getQueueHandlers($queue);

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Handle message "%s" from "%s"', $message, $queue->getName()));
        }

        foreach ($handlers as $handler) {
            try {
                $handler->handleMessage($message);
            } catch (\Exception $e) {
                if (null !== $this->logger) {
                    $this->logger->err(sprintf('{%s} %s: %s', $queue->getName(), get_class($e), $e->getMessage()));
                }
            }
        }
    }

    /**
     * Boot worker.
     *
     * @param integer $interval Intervals as millisecond
     */
    protected function boot($interval)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('boot queue worker (interval: %dms)', $interval));
        }

        $this->shutdown = false;
        $this->start = new \DateTime();
        $this->uinterval = $interval * 1000; // convert from millisecond to microsecond
    }

    /**
     * Shutdown worker.
     */
    protected function shutdown()
    {
        if (null !== $this->logger) {
            $this->logger->info('shutdown queue worker');
        }

        $this->start = null;
    }

    // Signal handling

    /**
     * Register this worker as a signal handler.
     */
    public function registerSignalHandler()
    {
        pcntl_signal(SIGTERM, array($this, 'handleSignal'));
        pcntl_signal(SIGINT, array($this, 'handleSignal'));
    }

    /**
     * Handle process signal.
     *
     * @param integer $signo
     */
    public function handleSignal($signo)
    {
        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Received signal "%s"', self::resolveSignalName($signo)));
        }

        switch ($signo) {
            case SIGTERM:
            case SIGINT:
                $this->shutdown = true;
                break;

            default:
                break;
        }
    }

    /**
     * Resolve signal constant name.
     *
     * @param integer $signo
     * @return string
     */
    protected static function resolveSignalName($signo)
    {
        static $names;

        if (!isset($names)) {
            $all = get_defined_constants(true);

            foreach ($all['pcntl'] as $name => $value) {
                if (0 === strpos($name, 'SIG') && '_' !== $name[3]) {
                    $names[$value] = $name;
                }
            }
        }

        return isset($names[$signo]) ? $names[$signo] : $signo;
    }
}
