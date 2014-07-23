<?php

namespace Tchess\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds services tagged 'access_check' to the access_manager service.
 */
class RegisterRulesPass implements CompilerPassInterface {

  /**
   * Implements CompilerPassInterface::process().
   *
   * Adds services tagged 'access_check' to the access_manager service.
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('dispatcher')) {
      return;
    }
    $dispatcher = $container->getDefinition('dispatcher');
    foreach ($container->findTaggedServiceIds('rules') as $id => $attributes) {
      $dispatcher->addMethodCall('addSubscriber', array(new Reference($id)));
    }
  }
}
