<?php

namespace Tchess\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterRulesPass implements CompilerPassInterface
{

    /**
     * Implements CompilerPassInterface::process().
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('dispatcher')) {
            $dispatcher = $container->getDefinition('dispatcher');
            foreach ($container->findTaggedServiceIds('rules') as $id => $attributes) {
                $dispatcher->addMethodCall('addSubscriber', array(new Reference($id)));
            }
        }

        if ($container->hasDefinition('validator.move')) {
            $validator = $container->getDefinition('validator.move');
            foreach ($container->findTaggedServiceIds('rules') as $id => $attributes) {
                $validator->addMethodCall('addRules', array(new Reference($id)));
            }
        }
    }

}
