<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Doctrine\Common\Annotations\AnnotationRegistry;

if (!in_array($env, array('prod', 'dev', 'test'))) {
    $env = 'prod';
}
$file = __DIR__ . '/../cache/container_' . $env . '.php';

if (file_exists($file)) {
    require_once $file;
    $sc = new ProjectServiceContainer();
} else {
    require_once __DIR__ . '/container_helper.php';

    $sc = new ContainerBuilder();

    register_db_services($sc, $config);

    register_kernel_services($sc, $env);

    register_chess_services($sc);

    register_twig_services($sc, $env);

    register_validator_services($sc);

    register_form_services($sc);

    register_serializer_services($sc);

    register_logger_services($sc);

    register_socket_services($sc);

    add_compiler_passes($sc);

    $sc->compile();

    $dumper = new PhpDumper($sc);
    file_put_contents($file, $dumper->dump());
}

// @todo - Run it using service container?
AnnotationRegistry::registerLoader('class_exists');

return $sc;
