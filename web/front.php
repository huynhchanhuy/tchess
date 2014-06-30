<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Tools\SchemaTool;

$env = 'prod';
$sc = include __DIR__ . '/../src/container.php';

// Check for installing.
$entityManager = $sc->get('entity_manager');
$schemaManager = $entityManager->getConnection()->getSchemaManager();
if ($schemaManager->tablesExist(array('room', 'game')) == false) {
    // getting objects.
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
    $schema_tool = new SchemaTool($entityManager);

    // drop all schemas.
    $schema_tool->dropSchema($metadatas);

    // recreate schemas
    $schema_tool->createSchema($metadatas);
}

$request = Request::createFromGlobals();

// Prepare session.
if (!$request->getSession()) {
    $session = new Session();
    $session->start();
    $request->setSession($session);
}

$sc->get('context')->fromRequest($request);

$response = $sc->get('framework')->handle($request);

$response->send();

$sc->get('framework')->terminate($request, $response);
