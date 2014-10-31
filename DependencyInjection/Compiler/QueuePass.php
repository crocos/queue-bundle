<?php

namespace Crocos\QueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class QueuePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('crocos_queue.queue_config')) {
            return;
        }

        $config = $container->getParameter('crocos_queue.queue_config');
        $definition = $container->getDefinition('crocos_queue.registry');

        foreach ($config as $name => $queue) {
            $definition->addMethodCall('registerQueue', array(new Reference($queue['id'])));
            foreach ($queue['handlers'] as $handler) {
                $definition->addMethodCall('registerQueueHandler', array(new Reference($queue['id']), new Reference($handler)));
            }
        }
    }
}
