<?php

/*
 * This file is a clone of Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConstraintValidatorsPass.
 */

namespace Tchess\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddConstraintValidatorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator.validator_factory')) {
            return;
        }

        $validators = array();
        foreach ($container->findTaggedServiceIds('validator.constraint_validator') as $id => $attributes) {
            if (isset($attributes[0]['alias'])) {
                $validators[$attributes[0]['alias']] = $id;
            }
        }

        $container->getDefinition('validator.validator_factory')->replaceArgument(1, $validators);
    }
}
