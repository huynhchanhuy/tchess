<?php

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\Common\Annotations\AnnotationRegistry;

$sc = new DependencyInjection\ContainerBuilder();

register_db_services($sc, $config);

register_kernel_services($sc, $env);

register_chess_services($sc);

register_twig_services($sc, $env);

register_form_services($sc);

function register_form_services($sc) {
    $sc->register('validator')
            ->setFactoryClass('Symfony\Component\Validator\Validation')
            ->setFactoryMethod('createValidator');
    $sc->register('form_validator_extension', 'Symfony\Component\Form\Extension\Validator\ValidatorExtension')
            ->setArguments(array(new Reference('validator')));

    $sc->register('form_http_foundation_extension', 'Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension');

    $sc->register('form_csrf_extension', 'Symfony\Component\Form\Extension\Csrf\CsrfExtension')
            ->setArguments(array(new Reference('csrf_provider')));

    $sc->register('form_factory_builder')
            ->setFactoryClass('Symfony\Component\Form\Forms')
            ->setFactoryMethod('createFormFactoryBuilder')
            ->addMethodCall('addExtension', array(new Reference('form_csrf_extension')))
            ->addMethodCall('addExtension', array(new Reference('form_http_foundation_extension')))
            ->addMethodCall('addExtension', array(new Reference('form_validator_extension')))
    ;

    $sc->register('form_factory')
            ->setFactoryService(new Reference('form_factory_builder'))
            ->setFactoryMethod('getFormFactory')
    ;
}

function register_twig_services($sc, $env) {
    $sc->register('url_generagor', 'Symfony\Component\Routing\Generator\UrlGenerator')
        ->setArguments(array('%routes%', new Reference('context')));
    $sc->register('twig_routing_extension', 'Symfony\Bridge\Twig\Extension\RoutingExtension')
            ->setArguments(array(new Reference('url_generagor')));

    $sc->setParameter('csrf_secret', 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr');
    $sc->register('csrf_provider', 'Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider')
            ->setArguments(array('%csrf_secret%'));
    $sc->setParameter('default_form_theme', 'form_div_layout.html.twig');
    $sc->register('twig_renderer_engine', 'Symfony\Bridge\Twig\Form\TwigRendererEngine')
            ->setArguments(array(array('%default_form_theme%')))
            ->addMethodCall('setEnvironment', array(new Reference('twig')))
    ;
    $sc->register('twig_renderer', 'Symfony\Bridge\Twig\Form\TwigRenderer')
            ->setArguments(array(new Reference('twig_renderer_engine'), new Reference('csrf_provider')));
    $sc->register('twig_form_extension', 'Symfony\Bridge\Twig\Extension\FormExtension')
            ->setArguments(array(new Reference('twig_renderer')));

    // Assets
    $sc->register('bootstrap_css_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/bootstrap.min.css'))
            ->addMethodCall('setTargetPath', array('css/bootstrap.min.css'));
    $sc->register('register_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/register/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/register.css'));
//    $sc->register('ie8_fix_js_asset', 'Assetic\Asset\FileAsset')
//            ->setArguments(array(__DIR__ . '/../resources/js/fix/ie8-responsive-file-warning.js'))
//            ->addMethodCall('setTargetPath', array('ie8_fix.js'));
//    $sc->register('images_glob_asset', 'Assetic\Asset\GlobAsset')
//            ->setArguments(array(__DIR__ . '/../resources/images/*'))
//            ->addMethodCall('setTargetPath', array('all.images'));

    $sc->register('asset_asset_manager', 'Assetic\AssetManager')
            ->addMethodCall('set', array('bootstrap', new Reference('bootstrap_css_asset')))
            ->addMethodCall('set', array('register', new Reference('register_css_glob_asset')))
//            ->addMethodCall('set', array('js', new Reference('js_glob_asset')))
//            ->addMethodCall('set', array('images', new Reference('images_glob_asset')))
    ;

    $sc->register('yui_css_compressor_filter', 'Assetic\Filter\Yui\CssCompressorFilter')
            ->setArguments(array(__DIR__ . '/../bin/yuicompressor-2.4.8.jar'));
    $sc->register('asset_filter_manager', 'Assetic\FilterManager')
            ->addMethodCall('set', array('yui_css', new Reference('yui_css_compressor_filter')));
    $sc->register('asset_writer', 'Assetic\AssetWriter')
            ->setArguments(array(__DIR__ . '/../web/resources'));
    $sc->setParameter('asset_root', __DIR__ . '/../resources');
    $sc->register('asset_factory', 'Assetic\Factory\AssetFactory')
            ->setArguments(array('%asset_root%'))
            ->addMethodCall('setAssetManager', array(new Reference('asset_asset_manager')))
            ->addMethodCall('setFilterManager', array(new Reference('asset_filter_manager')))
            ->addMethodCall('setDebug', array($env == 'dev' ? true : false))
    ;
    $sc->register('twig_assetic_extension', 'Assetic\Extension\Twig\AsseticExtension')
            ->setArguments(array(new Reference('asset_factory')));
    $sc->register('asset_function', 'Twig_SimpleFunction')
            ->setArguments(array('asset', function ($asset) {
                // implement whatever logic you need to determine the asset path

                return sprintf('http://assets.examples.com/%s', ltrim($asset, '/'));
            }));

    $sc->register('xliff_file_loader', 'Symfony\Component\Translation\Loader\XliffFileLoader');
    $sc->register('translator', 'Symfony\Component\Translation\Translator')
            ->setArguments(array('en'))
            ->addMethodCall('addLoader', array('xlf', new Reference('xliff_file_loader')))
            ->addMethodCall('addResource', array('xlf', __DIR__ . '/../vendor/symfony/form/Symfony/Component/Form/Resources/translations/validators.en.xlf', 'en', 'validators'))
            ->addMethodCall('addResource', array('xlf', __DIR__ . '/../vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators.en.xlf', 'en', 'validators'))
    ;
    $sc->register('twig_translation_extension', 'Symfony\Bridge\Twig\Extension\TranslationExtension')
            ->setArguments(array(new Reference('translator')));

    Twig_Autoloader::register();

    $twig_options = array(
        'cache' => __DIR__ . '/../cache'
    );
    if ($env == 'dev') {
        $twig_options['debug'] = true;
    }
    $sc->setParameter('twig_options', $twig_options);

    $sc->register('twig_loader', 'Twig_Loader_Filesystem')
            ->setArguments(array(array(
                __DIR__ . '/../templates',
                __DIR__ . '/../vendor/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form'
            )));
    $sc->register('twig', 'Twig_Environment')
            ->setArguments(array(new Reference('twig_loader'), '%twig_options%'))
            ->addMethodCall('addExtension', array(new Reference('twig_routing_extension')))
            ->addMethodCall('addExtension', array(new Reference('twig_form_extension')))
            ->addMethodCall('addExtension', array(new Reference('twig_assetic_extension')))
            ->addMethodCall('addExtension', array(new Reference('twig_translation_extension')))
            ->addMethodCall('addFunction', array('asset', new Reference('asset_function')))
    ;
}

function register_chess_services($sc) {
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

    $sc->getDefinition('dispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.game')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.basic')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.pawn')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.bishop')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.king')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.knight')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.rook')))
            ->addMethodCall('addSubscriber', array(new Reference('rules.in_check')))
        ;
}

function register_kernel_services($sc, $env) {
    $sc->register('locator', 'Symfony\Component\Config\FileLocator')
        ->setArguments(array(__DIR__ . '/../config'));
    $sc->register('yaml_loader', 'Symfony\Component\Routing\Loader\YamlFileLoader')
            ->setArguments(array(new Reference('locator')));

    $routes = $sc->get('yaml_loader')->load('routes.yml');
    $sc->setParameter('routes', $routes);

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

    $sc->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            // @todo - This method 'addSubscriber' can be called automatically via
            // compiler pass.
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response.string')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.controller')))
    ;


    if ($env == 'prod') {
        $sc->register('listener.exception', 'Symfony\Component\HttpKernel\EventListener\ExceptionListener')
                ->setArguments(array('Tchess\\Controller\\ErrorController::exceptionAction'))
        ;
        $sc->getDefinition('dispatcher')->addMethodCall('addSubscriber', array(new Reference('listener.exception')));
    }

    $sc->register('framework', 'Tchess\Framework')
            ->setArguments(array(new Reference('dispatcher'), new Reference('resolver')))
            // @todo - Don't inject the container.
            ->addMethodCall('setContainer', array($sc))
    ;
}

function register_db_services($sc, $config) {
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
}

return $sc;
