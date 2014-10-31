<?php

namespace Crocos\QueueBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CrocosQueueExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (!empty($config['queues'])) {
            $this->registerQueuesConfiguration($config['queues'], $container, $loader);
        }
    }

    /**
     * Loads the router configuration.
     *
     * @param array            $config    A queue configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader   $loader    A YamlFileLoader instance
     */
    protected function registerQueuesConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('queue.yml');

        $container->setParameter('crocos_queue.queue_config', $config);

        // Actual queue services building is performed by a QueueCompilerPass using above config parameter
    }
}
