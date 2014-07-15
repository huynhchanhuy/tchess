<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\Tools\SchemaTool;

/** Global variables **/
$config = include __DIR__ . '/../config/db-config.php';
$config['path'] = str_replace('%root_dir%', __DIR__ . '/..', $config['path']);
$env = 'prod';

$sc = include __DIR__ . '/../src/container.php';

// getting objects.
$entityManager = $sc->get('entity_manager');
$metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
$schema_tool = new SchemaTool($entityManager);

// drop all schemas.
$schema_tool->dropSchema($metadatas);

// recreate schemas
$schema_tool->createSchema($metadatas);
