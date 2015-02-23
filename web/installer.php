<?php

// Check the database config
if (file_exists('../config/db-config.php') && strpos(file_get_contents('../config/db-config.php'), '%db.database%') === false) {
    header("Location: index.php", TRUE, 303);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\DriverManager;

// Get values from the form
$driver = $_POST['driver'];
$path = $_POST['path'];
$charset = $_POST['charset'];
$config_content = file_get_contents('../config/db-config.php.dist');
$configured_config_content = str_replace(array(
    '%db.driver%',
    '%db.path%',
    '%db.charset%',
), array(
    $driver,
    $path,
    $charset
), $config_content);
file_put_contents(__DIR__ . '/../config/db-config.php', $configured_config_content);

/** Global variables **/
$config = include __DIR__ . '/../config/db-config.php';
$config['path'] = str_replace('%root_dir%', __DIR__ . '/..', $config['path']);
$env = 'prod';

$sc = include __DIR__ . '/../src/container.php';

// Create database if needed.
// @see - https://github.com/doctrine/DoctrineBundle/blob/master/Command/CreateDatabaseDoctrineCommand.php
$connection = DriverManager::getConnection($config);
$name = $config['path'];
$connection->close();
$connection->getSchemaManager()->dropDatabase($name);
$connection->getSchemaManager()->createDatabase($name);

// Getting schema tool object.
$entityManager = $sc->get('entity_manager');
$metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
$schema_tool = new SchemaTool($entityManager);

// Drop all schemas.
$schema_tool->dropSchema($metadatas);

// Recreate schemas
$schema_tool->createSchema($metadatas);

// Write assets to web directory to avoid 404 errors.
$sc->get('asset_writer')->writeManagerAssets($sc->get('asset_asset_manager'));

header('Content-type: text/html; charset=utf-8', true, 200);

echo '<h2>Installed</h2>';
echo 'Tchess has been installed successfully.  Back to the <a href="index.php">game</a>.';
