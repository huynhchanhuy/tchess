<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

$config = include __DIR__ . '/db-config.php';
$config['path'] = str_replace('%root_dir%', __DIR__ . '/..', $config['path']);

$env = 'dev';
$sc = include __DIR__ . '/../src/container.php';

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $sc->get('entity_manager');

return ConsoleRunner::createHelperSet($entityManager);
