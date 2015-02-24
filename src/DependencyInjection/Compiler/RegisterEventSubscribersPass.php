<?php

namespace Tchess\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterEventSubscribersPass implements CompilerPassInterface
{

    /**
     * Implements CompilerPassInterface::process().
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('dispatcher')) {
            $dispatcher = $container->getDefinition('dispatcher');
            foreach ($container->findTaggedServiceIds('event_subscriber') as $id => $attributes) {
                $dispatcher->addMethodCall('addSubscriber', array(new Reference($id)));
            }
        }
    }

}
