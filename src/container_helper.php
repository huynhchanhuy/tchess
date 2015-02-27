<?php

use Symfony\Component\DependencyInjection\Reference;
use Monolog\Logger;
use Tchess\DependencyInjection\Compiler\RegisterRulesPass;
use Tchess\DependencyInjection\Compiler\AddConstraintValidatorsPass;
use Tchess\DependencyInjection\Compiler\RegisterEventSubscribersPass;

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

function register_validator_services($sc)
{
    $sc->setParameter('validator.class', 'Symfony\Component\Validator\ValidatorInterface');
    $sc->setParameter('validator.builder.class', 'Symfony\Component\Validator\ValidatorBuilderInterface');
    $sc->setParameter('validator.builder.factory.class', 'Symfony\Component\Validator\Validation');
    $sc->setParameter('validator.validator_factory.class', 'Tchess\Validator\ConstraintValidatorFactory');
    $sc->register('validator')
            ->setClass('%validator.class%')
            ->setFactoryService(new Reference('validator.builder'))
            ->setFactoryMethod('getValidator');
    $sc->register('validator.validator_factory')
            ->setClass('%validator.validator_factory.class%')
            ->setPublic(false)
            ->setArguments(array(new Reference('service_container'), array()));
    $sc->register('validator.builder')
            ->setClass('%validator.builder.class%')
            ->setFactoryClass('Symfony\Component\Validator\Validation')
            ->setFactoryMethod('createValidatorBuilder')
            ->addMethodCall('setTranslator', array(new Reference('translator')))
            ->addMethodCall('enableAnnotationMapping', array(new Reference('annotation_reader')))
            ->addMethodCall('setConstraintValidatorFactory', array(new Reference('validator.validator_factory')));

    $sc->register('constraint_validator.move', 'Tchess\Validator\Constraints\MoveValidator')
            ->addTag('validator.constraint_validator', array('alias' => 'constraint_validator.move'))
            ->setScope('prototype');
}

function register_form_services($sc)
{
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
    // Use SessionCsrfProvider make it easier to test.
    // @see - http://stackoverflow.com/a/17223104
    $sc->register('csrf_provider', 'Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider')
            ->setArguments(array(new Reference('session'), '%csrf_secret%'));

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
    $sc->register('nav_css_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/nav.css'))
            ->addMethodCall('setTargetPath', array('css/nav.css'));
    $sc->register('register_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/register/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/register.css'));
    $sc->register('board_css_glob_asset', 'Assetic\Asset\GlobAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/board/*', array(new Reference('yui_css_compressor_filter'))))
            ->addMethodCall('setTargetPath', array('css/board.css'));
    $sc->register('status_css_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/css/status.css'))
            ->addMethodCall('setTargetPath', array('css/status.css'));
    $sc->register('chess_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess.js'))
            ->addMethodCall('setTargetPath', array('js/chess.js'));
    $sc->register('chess_remote_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess-remote.js'))
            ->addMethodCall('setTargetPath', array('js/chess-remote.js'));
    $sc->register('chess_practice_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess-practice.js'))
            ->addMethodCall('setTargetPath', array('js/chess-practice.js'));
    $sc->register('chess_watch_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess-watch.js'))
            ->addMethodCall('setTargetPath', array('js/chess-watch.js'));
    $sc->register('chess_play_js_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/js/chess-play.js'))
            ->addMethodCall('setTargetPath', array('js/chess-play.js'));
    $sc->register('favicon_asset', 'Assetic\Asset\FileAsset')
            ->setArguments(array(__DIR__ . '/../resources/images/favicon.ico'))
            ->addMethodCall('setTargetPath', array('images/favicon.ico'));

    $sc->register('asset_asset_manager', 'Assetic\AssetManager')
            ->addMethodCall('set', array('nav', new Reference('nav_css_asset')))
            ->addMethodCall('set', array('register_css', new Reference('register_css_glob_asset')))
            ->addMethodCall('set', array('board_css', new Reference('board_css_glob_asset')))
            ->addMethodCall('set', array('status', new Reference('status_css_asset')))
            ->addMethodCall('set', array('chess_js', new Reference('chess_js_asset')))
            ->addMethodCall('set', array('chess_remote_js', new Reference('chess_remote_js_asset')))
            ->addMethodCall('set', array('chess_practice_js', new Reference('chess_practice_js_asset')))
            ->addMethodCall('set', array('chess_watch_js', new Reference('chess_watch_js_asset')))
            ->addMethodCall('set', array('chess_play_js', new Reference('chess_play_js_asset')))
            ->addMethodCall('set', array('favicon', new Reference('favicon_asset')))
    ;

    // jui compressor.
    $sc->register('yui_css_compressor_filter', 'Assetic\Filter\Yui\CssCompressorFilter')
            ->setArguments(array(__DIR__ . '/../vendor/bin/yuicompressor.jar'));
    $sc->register('yui_js_compressor_filter', 'Assetic\Filter\Yui\JsCompressorFilter')
            ->setArguments(array(__DIR__ . '/../vendor/bin/yuicompressor.jar'));

    $sc->register('asset_filter_manager', 'Assetic\FilterManager')
            ->addMethodCall('set', array('yui_css', new Reference('yui_css_compressor_filter')))
            ->addMethodCall('set', array('yui_js', new Reference('yui_js_compressor_filter')));
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
            ->setArguments(array(new Reference('entity_manager'), new Reference('serializer'), new Reference('logger'), new Reference('message_manager')))
            ->addMethodCall('setSocket', array(new Reference('socket')))
            ->addTag('event_subscriber');

    $sc->register('rules.basic', 'Tchess\Rule\BasicRules')
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.pawn', 'Tchess\Rule\PawnRules')
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.bishop', 'Tchess\Rule\BishopRules')
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.king', 'Tchess\Rule\KingRules')
            ->setArguments(array(new Reference('message_manager'), new Reference('rules.in_check')))
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.knight', 'Tchess\Rule\KnightRules')
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.rook', 'Tchess\Rule\RookRules')
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.queen', 'Tchess\Rule\QueenRules')
            ->setArguments(array(new Reference('rules.bishop'), new Reference('rules.rook')))
            ->addTag('event_subscriber')
            ->addTag('rules');
    $sc->register('rules.in_check', 'Tchess\Rule\InCheckRules')
            ->setArguments(array(new Reference('validator')))
            ->addTag('event_subscriber')
            ->addTag('rules');

    $sc->register('message_manager', 'Tchess\MessageManager');
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
            ->addTag('event_subscriber')
    ;
    $sc->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
            ->setArguments(array('UTF-8'))
            ->addTag('event_subscriber')
    ;
    $sc->register('listener.response.string', 'Tchess\EventListener\StringResponseListener')
            ->addTag('event_subscriber');
    $sc->register('listener.controller', 'Tchess\EventListener\ControllerListener')
            ->setArguments(array(new Reference('service_container')))
            ->addTag('event_subscriber');
    $sc->register('listener.session', 'Tchess\EventListener\SessionListener')
            ->addMethodCall('setSession', array(new Reference('session')))
            ->addTag('event_subscriber');

    if ($env == 'test') {
        $sc->register('session.storage', 'Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage');
    }
    else {
        $sc->register('session.storage', 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage');
    }
    $sc->register('session', 'Symfony\Component\HttpFoundation\Session\Session')
            ->setArguments(array(new Reference('session.storage')));

    $sc->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher');

    $sc->register('kernel', 'Symfony\Component\HttpKernel\HttpKernel')
            ->setArguments(array(new Reference('dispatcher'), new Reference('resolver')))
    ;
}

function register_db_services($sc, $config)
{
    $sc->setParameter('entity_paths', array(__DIR__ . '/Entity'));

    $sc->setParameter('db_config', $config);

    $sc->register('entity_config')
            ->setFactoryClass('Doctrine\ORM\Tools\Setup')
            ->setFactoryMethod('createConfiguration')
            ->setClass('Doctrine\ORM\Configuration')
            ->addMethodCall('setMetadataDriverImpl', array(new Reference('annotation_driver')))
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

function register_socket_services($sc)
{
    if (!(class_exists('\ZMQContext')) || !(class_exists('\ZMQSocket')) || !(class_exists('\ZMQ'))) {
        return;
    }

    $sc->register('zmq_context', 'ZMQContext');

    $sc->register('socket')
            ->setFactoryService(new Reference('zmq_context'))
            ->setFactoryMethod('getSocket')
            ->setClass('ZMQSocket')
            ->setArguments(array(\ZMQ::SOCKET_PUSH, 'my pusher'))
            ->addMethodCall('connect', array('tcp://localhost:5555'))
    ;
}

function add_compiler_passes($sc)
{
    $sc->addCompilerPass(new RegisterEventSubscribersPass());
    $sc->addCompilerPass(new RegisterRulesPass());
    $sc->addCompilerPass(new AddConstraintValidatorsPass());
}

