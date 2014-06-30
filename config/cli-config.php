<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$env = 'prod';
$sc = include __DIR__ . '/../src/container.php';

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $sc->get('entity_manager');

return ConsoleRunner::createHelperSet($entityManager);
