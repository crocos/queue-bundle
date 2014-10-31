CrocosQueueBundle - README
===========================

Provides simple message queueing.

Configuration
----------------

### app/AppKernel.php

Register `CrocosQueueBundle` on `AppKernel`.

    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Crocos\QueueBundle\CrocosQueueBundle(),
        );

        // ...
    }

### app/config/config.yml

Configure `crocos_queue`.

    crocos_queue:
        queues:
            item_update:
                id: myapp.queue.foo
                handlers:
                    - myapp.queue.handler.foo

### YourBundle/Resoruces/config/services.yml

Register queue services.

    services:
        myapp.queue.foo:
            class: Crocos\QueueBundle\Queue\DoctrineQueue
            arguments:
                - "table_name_for_foo_queue"
                - @doctrine.dbal.default_connection

        myapp.queue.handler.foo:
            class: Crocos\MyAppBundle\Queue\FooHandler

### Create queue table

If you want to use the `DoctrineQueue`, you need to create a queue table manually.
In such case, queue table should contain 3 fields.

- *id*: message identifier (should be auto-increment)
- *message*: message body
- *priority*: message priority

The following query is a sample DDL for MySQL.

    DROP TABLE IF EXISTS `table_name_for_foo_queue`;
    CREATE TABLE `table_name_for_foo_queue` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `message` VARCHAR(255) NOT NULL,
        `priority` TINYINT NOT NULL DEFAULT 10,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


Message queuing
-------------------

If you want to enqueue message, you can use the `enqueue()` method.

    $queue->enqueue($message = 'message', $priority = 10);

And also if you want to dequeue message, you can use the `dequeue()` method.

    $message = $queue->enqueue();

Queue handler
--------------

To handle the inserted messages, you should create the queue handler.
The queue handler must implement the `QueueHandlerInterface` interface.

    <?php

    namespace Crocos\MyAppBundle\Queue;

    use Crocos\QueueBundle\Queue\QueueHandlerInterface;

    class FooHandler implements QueueHandlerInterface
    {
        public function handleMessage($message)
        {
            // do something you need.
        }
    }


Observe queues
---------------

First, you need to copy a queue script from `CrocosQueueBundle`.

    $ cd /path/to/app
    $ cp src/Crocos/QueueBundle/Resources/skeleton/queue app/queue

And then run a queue script.

    $ php app/queue

### Available options

- *--env*: Application environment. (default: dev)
- *--debug*: Enable debug mode if this option specified.
