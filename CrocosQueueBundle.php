<?php

namespace Crocos\QueueBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Crocos\QueueBundle\DependencyInjection\Compiler\QueuePass;

class CrocosQueueBundle extends Bundle
{
    /**
     * Build Container.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new QueuePass());
    }
}
