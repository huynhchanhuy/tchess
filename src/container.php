<?php

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\Common\Annotations\AnnotationRegistry;

$sc = new DependencyInjection\ContainerBuilder();

$sc->register('locator', 'Symfony\Component\Config\FileLocator')
        ->setArguments(array(__DIR__ . '/../config'));
$sc->register('yaml_loader', 'Symfony\Component\Routing\Loader\YamlFileLoader')
        ->setArguments(array(new Reference('locator')));

$routes = $sc->get('yaml_loader')->load('routes.yml');
$sc->setParameter('routes', $routes);

$sc->setParameter('entity_paths', array(__DIR__ . '/Entity'));

$sc->setParameter('db_config', $config);

$sc->register('array_cache', 'Doctrine\Common\Cache\ArrayCache');

$sc->register('entity_config')
        ->setFactoryClass('Doctrine\ORM\Tools\Setup')
        ->setFactoryMethod('createConfiguration')
        ->addMethodCall('setMetadataDriverImpl', array(new Reference('annotation_driver')))
        ->addMethodCall('setMetadataCacheImpl', array(new Reference('array_cache')))
        ->addMethodCall('setResultCacheImpl', array(new Reference('array_cache')))
        ->addMethodCall('setQueryCacheImpl', array(new Reference('array_cache')));

$sc->register('annotation_reader', 'Doctrine\Common\Annotations\AnnotationReader');

$sc->register('annotation_driver', 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')
        ->setArguments(array(new Reference('annotation_reader'), $sc->getParameter('entity_paths')));

// @todo - Run it using service container?
AnnotationRegistry::registerLoader('class_exists');

$sc->register('entity_manager')
        ->setFactoryClass('Doctrine\ORM\EntityManager')
        ->setFactoryMethod('create')
        ->setArguments(array('%db_config%', new Reference('entity_config')));

$sc->register('context', 'Symfony\Component\Routing\RequestContext');
$sc->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')
        ->setArguments(array('%routes%', new Reference('context')))
;
$sc->register('resolver', 'Symfony\Component\HttpKernel\Controller\ControllerResolver');

$sc->register('listener.router', 'Symfony\Component\HttpKernel\EventListener\RouterListener')
        ->setArguments(array(new Reference('matcher')))
;
$sc->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
        ->setArguments(array('UTF-8'))
;
$sc->register('listener.response.string', 'Tchess\EventListener\StringResponseListener');
$sc->register('listener.controller', 'Tchess\EventListener\ControllerListener');
$sc->register('listener.game', 'Tchess\EventListener\GameListener')
        ->setArguments(array(new Reference('entity_manager')));
$sc->register('rules.basic', 'Tchess\Rule\BasicRules');
$sc->register('rules.pawn', 'Tchess\Rule\PawnRules');
$sc->register('rules.bishop', 'Tchess\Rule\BishopRules');
$sc->register('rules.king', 'Tchess\Rule\KingRules');
$sc->register('rules.knight', 'Tchess\Rule\KnightRules');
$sc->register('rules.rook', 'Tchess\Rule\RookRules');
$sc->register('rules.in_check', 'Tchess\Rule\InCheckRules')
        ->setArguments(array(new Reference('dispatcher')));

$sc->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
        // @todo - This method 'addSubscriber' can be called automatically via
        // compiler pass.
        ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
        ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
        ->addMethodCall('addSubscriber', array(new Reference('listener.response.string')))
        ->addMethodCall('addSubscriber', array(new Reference('listener.controller')))
        ->addMethodCall('addSubscriber', array(new Reference('listener.game')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.basic')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.pawn')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.bishop')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.king')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.knight')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.rook')))
        ->addMethodCall('addSubscriber', array(new Reference('rules.in_check')))
;


if ($env == 'prod') {
    $sc->register('listener.exception', 'Symfony\Component\HttpKernel\EventListener\ExceptionListener')
            ->setArguments(array('Tchess\\Controller\\ErrorController::exceptionAction'))
    ;
    $sc->getDefinition('dispatcher')->addMethodCall('addSubscriber', array(new Reference('listener.exception')));
}

$sc->register('url_generagor', 'Symfony\Component\Routing\Generator\UrlGenerator')
        ->setArguments(array('%routes%', new Reference('context')));
$sc->register('twig_routing_extension', 'Symfony\Bridge\Twig\Extension\RoutingExtension')
        ->setArguments(array(new Reference('url_generagor')));

$sc->register('twig_renderer_engine', 'Symfony\Bridge\Twig\Form\TwigRendererEngine');
$sc->register('twig_renderer', 'Symfony\Bridge\Twig\Form\TwigRenderer')
        ->setArguments(array(new Reference('twig_renderer_engine')));
$sc->register('twig_form_extension', 'Symfony\Bridge\Twig\Extension\FormExtension')
        ->setArguments(array(new Reference('twig_renderer')));

$sc->setParameter('asset_root', __DIR__ . '/../resources');
$sc->register('asset_factory', 'Assetic\Factory\AssetFactory')
        ->setArguments(array('%asset_root%', $env == 'dev' ? true : false));
$sc->register('twig_assetic_extension', 'Assetic\Extension\Twig\AsseticExtension')
        ->setArguments(array(new Reference('asset_factory')));
$sc->register('asset_function', 'Twig_SimpleFunction')
        ->setArguments(array('asset', function ($asset) {
            // implement whatever logic you need to determine the asset path

            return sprintf('http://assets.examples.com/%s', ltrim($asset, '/'));
        }));

Twig_Autoloader::register();

$twig_options = array(
    'cache' => __DIR__ . '/../cache'
);
if ($env == 'dev') {
    $twig_options['debug'] = true;
}
$sc->setParameter('twig_options', $twig_options);

$sc->register('twig_loader', 'Twig_Loader_Filesystem')
        ->setArguments(array(__DIR__ . '/../templates'));
$sc->register('twig', 'Twig_Environment')
        ->setArguments(array(new Reference('twig_loader'), '%twig_options%'))
        ->addMethodCall('addExtension', array(new Reference('twig_routing_extension')))
        ->addMethodCall('addExtension', array(new Reference('twig_form_extension')))
        ->addMethodCall('addExtension', array(new Reference('twig_assetic_extension')))
        ->addMethodCall('addFunction', array('asset', new Reference('asset_function')))
;

$sc->register('framework', 'Tchess\Framework')
        ->setArguments(array(new Reference('dispatcher'), new Reference('resolver')))
        // @todo - Don't inject the container.
        ->addMethodCall('setContainer', array($sc))
;

return $sc;
