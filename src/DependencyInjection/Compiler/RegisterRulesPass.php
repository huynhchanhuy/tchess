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
        if ($container->hasDefinition('constraint_validator.move')) {
            $moveValidator = $container->getDefinition('constraint_validator.move');
            foreach ($container->findTaggedServiceIds('rules') as $id => $attributes) {
                $moveValidator->addMethodCall('addRules', array(new Reference($id)));
            }
        }
    }

}
