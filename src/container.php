<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Logger;
use Tchess\DependencyInjection\Compiler\RegisterRulesPass;

$file = __DIR__ . '/../cache/container.php';

if (file_exists($file)) {
    require_once $file;
    $sc = new ProjectServiceContainer();
} else {
    $sc = new ContainerBuilder();

    register_db_services($sc, $config);

    register_kernel_services($sc, $env);

    register_chess_services($sc);

    register_twig_services($sc, $env);

    register_form_services($sc);

    register_serializer_services($sc);

    register_logger_services($sc);

    add_compiler_passes($sc);

    $sc->compile();

    $dumper = new PhpDumper($sc);
    file_put_contents($file, $dumper->dump());
}

function add_compiler_passes($sc)
{
    $sc->addCompilerPass(new RegisterRulesPass());
}

// @todo - Run it using service container?
AnnotationRegistry::registerLoader('class_exists');

return $sc;

function register_logger_services($sc)
{
    $sc->register('logger', 'Monolog\Logger')
            ->setArguments(array('moves'))
            ->addMethodCall('pushHandler', array(new Reference('logger_stream_handler')))
    ;
    $sc->register('logger_stream_handler', 'Monolog\Handler\StreamHandler')
            ->setArguments(array(__DIR__ . '/../logs/moves.log', Logger::INFO));
}

function register_serializer_services($sc)
{
    $sc->register('fen_encoder', 'Tchess\Serializer\Encoder\FenEncoder');
    $sc->register('board_pieces_normalizer', 'Tchess\Serializer\Normalizer\BoardPiecesNormalizer');
    $sc->register('serializer', 'Symfony\Component\Serializer\Serializer')
            ->setArguments(array(array(new Reference('board_pieces_normalizer')), array(new Reference('fen_encoder'))));
}

function register_form_services($sc)
{
    $sc->register('validator')
            ->setFactoryClass('Symfony\Component\Validator\Validation')
            ->setFactoryMethod('createValidator')
            ->setClass('Symfony\Component\Validator\Validator');
    $sc->register('form_validator_extension', 'Symfony\Component\Form\Extension\Validator\ValidatorExtension')
            ->setArguments(array(new Reference('validator')));

    $sc->register('form_http_foundation_extension', 'Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension');

    $sc->register('form_csrf_extension', 'Symfony\Component\Form\Extension\Csrf\CsrfExtension')
            ->setArguments(array(new Reference('csrf_provider')));

    $sc->register('form_factory_builder')
            ->setFactoryClass('Symfony\Component\Form\Forms')
            ->setFactoryMethod('createFormFactoryBuilder')
            ->setClass('Symfony\Component\Form\FormFactoryBuilder')
            ->addMethodCall('addExtension', array(new Reference('form_csrf_extension')))
            ->addMethodCall('addExtension', array(new Reference('form_http_foundation_extension')))
            ->addMethodCall('addExtension', array(new Reference('form_validator_extension')))
    ;

    $sc->register('form_factory')
            ->setFactoryService(new Reference('form_factory_builder'))
            ->setFactoryMethod('getFormFactory')
            ->setClass('Symfony\Component\Form\FormFactory')
    ;
}

function register_twig_services($sc, $env)
{
    $sc->register('url_generator', 'Symfony\Component\Routing\Generator\UrlGenerator')
            ->setArguments(array(new Reference('route_collection'), new Reference('context')));
    $sc->register('twig_routing_extension', 'Symfony\Bridge\Twig\Extension\RoutingExtension')
            ->setArguments(array(new Reference('url_generator')));

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
    $sc->register('nav_css_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/nav.css'))
            ->addMethodCall('setTargetPath', array('css/nav.css'));
    $sc->register('register_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/register/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/register.css'));
    $sc->register('homepage_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/homepage/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/homepage.css'));
    $sc->register('board_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/board/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/board.css'));
    $sc->register('chess_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess.js'))
            ->addMethodCall('setTargetPath', array('js/chess.js'));
    $sc->register('chess_practice_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess-practice.js'))
            ->addMethodCall('setTargetPath', array('js/chess-practice.js'));
    $sc->register('game_buttons_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/game-buttons.js'))
            ->addMethodCall('setTargetPath', array('js/game-buttons.js'));
    $sc->register('bootstrap_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/bootstrap.min.js'))
            ->addMethodCall('setTargetPath', array('js/bootstrap.min.js'));
    $sc->register('favicon_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/images/favicon.ico'))
            ->addMethodCall('setTargetPath', array('images/favicon.ico'));

    $sc->register('asset_asset_manager', 'Assetic\AssetManager')
            ->addMethodCall('set', array('bootstrap', new Reference('bootstrap_css_asset')))
            ->addMethodCall('set', array('bootstrap', new Reference('nav_css_asset')))
            ->addMethodCall('set', array('register_css', new Reference('register_css_glob_asset')))
            ->addMethodCall('set', array('homepage_css', new Reference('homepage_css_glob_asset')))
            ->addMethodCall('set', array('board_css', new Reference('board_css_glob_asset')))
            ->addMethodCall('set', array('chess_js', new Reference('chess_js_asset')))
            ->addMethodCall('set', array('chess_js', new Reference('chess_practice_js_asset')))
            ->addMethodCall('set', array('game_buttons_js', new Reference('game_buttons_js_asset')))
            ->addMethodCall('set', array('bootstrap_js', new Reference('bootstrap_js_asset')))
            ->addMethodCall('set', array('favicon', new Reference('favicon_asset')))
    ;

    $sc->register('yui_css_compressor_filter', 'Assetic\Filter\Yui\CssCompressorFilter')
            ->setArguments(array(__DIR__ . '/../vendor/bin/yuicompressor.jar'));
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

    $sc->register('xliff_file_loader', 'Symfony\Component\Translation\Loader\XliffFileLoader');
    $sc->register('translator', 'Symfony\Component\Translation\Translator')
            ->setArguments(array('en'))
            ->addMethodCall('addLoader', array('xlf', new Reference('xliff_file_loader')))
            ->addMethodCall('addResource', array('xlf', __DIR__ . '/../vendor/symfony/form/Symfony/Component/Form/Resources/translations/validators.en.xlf', 'en', 'validators'))
            ->addMethodCall('addResource', array('xlf', __DIR__ . '/../vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators.en.xlf', 'en', 'validators'))
    ;
    $sc->register('twig_translation_extension', 'Symfony\Bridge\Twig\Extension\TranslationExtension')
            ->setArguments(array(new Reference('translator')));

    // @todo - Run it using service container?
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
    ;
}

function register_chess_services($sc)
{
    $sc->register('listener.game', 'Tchess\EventListener\GameListener')
            ->setArguments(array(new Reference('entity_manager'), new Reference('serializer'), new Reference('logger'), new Reference('move_manager')));

    $sc->register('rules.basic', 'Tchess\Rule\BasicRules')
            ->addTag('rules');
    $sc->register('rules.pawn', 'Tchess\Rule\PawnRules')
            ->addTag('rules');
    $sc->register('rules.bishop', 'Tchess\Rule\BishopRules')
            ->addTag('rules');
    $sc->register('rules.king', 'Tchess\Rule\KingRules')
            ->setArguments(array(new Reference('move_manager')))
            ->addTag('rules');
    $sc->register('rules.knight', 'Tchess\Rule\KnightRules')
            ->addTag('rules');
    $sc->register('rules.rook', 'Tchess\Rule\RookRules')
            ->addTag('rules');
    $sc->register('rules.queen', 'Tchess\Rule\QueenRules')
            ->setArguments(array(new Reference('rules.bishop'), new Reference('rules.rook')))
            ->addTag('rules');
    $sc->register('rules.in_check', 'Tchess\Rule\InCheckRules')
            ->setArguments(array(new Reference('dispatcher')))
            ->addTag('rules');

    $sc->getDefinition('dispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.game')))
    ;

    $sc->register('move_manager', 'Tchess\MoveManager');
}

function register_kernel_services($sc, $env)
{
    $sc->register('locator', 'Symfony\Component\Config\FileLocator')
            ->setArguments(array(__DIR__ . '/../config'));
    $sc->register('yaml_loader', 'Symfony\Component\Routing\Loader\YamlFileLoader')
            ->setArguments(array(new Reference('locator')));

    $sc->register('route_collection')
            ->setFactoryService(new Reference('yaml_loader'))
            ->setFactoryMethod('load')
            ->setClass('Symfony\Component\Routing\RouteCollection')
            ->setArguments(array('routes.yml'));

    $sc->register('context', 'Symfony\Component\Routing\RequestContext');
    $sc->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')
            ->setArguments(array(new Reference('route_collection'), new Reference('context')))
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
            ->addMethodCall('setEntityManager', array(new Reference('entity_manager')))
            ->addMethodCall('setFormFactory', array(new Reference('form_factory')))
            ->addMethodCall('setMoveManager', array(new Reference('move_manager')))
            ->addMethodCall('setSerializer', array(new Reference('serializer')))
            ->addMethodCall('setTwig', array(new Reference('twig')))
            ->addMethodCall('setUrlGenerator', array(new Reference('url_generator')))
    ;
}

function register_db_services($sc, $config)
{
    $sc->setParameter('entity_paths', array(__DIR__ . '/Entity'));

    $sc->setParameter('db_config', $config);

    $sc->register('array_cache', 'Doctrine\Common\Cache\ArrayCache');

    $sc->register('entity_config')
            ->setFactoryClass('Doctrine\ORM\Tools\Setup')
            ->setFactoryMethod('createConfiguration')
            ->setClass('Doctrine\ORM\Configuration')
            ->addMethodCall('setMetadataDriverImpl', array(new Reference('annotation_driver')))
            ->addMethodCall('setMetadataCacheImpl', array(new Reference('array_cache')))
            ->addMethodCall('setResultCacheImpl', array(new Reference('array_cache')))
            ->addMethodCall('setQueryCacheImpl', array(new Reference('array_cache')))
            ->addMethodCall('setProxyDir', array(__DIR__ . '/../files/proxies'))
            ->addMethodCall('setProxyNamespace', array('EntityProxy'))
            ->addMethodCall('setAutoGenerateProxyClasses', array(true))
    ;

    $sc->register('annotation_reader', 'Doctrine\Common\Annotations\AnnotationReader');

    $sc->register('annotation_driver', 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ->setArguments(array(new Reference('annotation_reader'), $sc->getParameter('entity_paths')));

    $sc->register('entity_manager')
            ->setFactoryClass('Doctrine\ORM\EntityManager')
            ->setFactoryMethod('create')
            ->setClass('Doctrine\ORM\EntityManager')
            ->setArguments(array('%db_config%', new Reference('entity_config')));
}
