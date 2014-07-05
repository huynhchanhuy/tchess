<?php

// Check the database config
if (!file_exists('../config/db-config.php') || strpos(file_get_contents('../config/db-config.php'), '%db.database%') !== false) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Tchess must be installed first. Please run the <a href="install.html">installer</a>.';

    return;
}

require_once __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__ . '/../config/db-config.php';
$config['path'] = str_replace('%root_dir%', __DIR__ . '/..', $config['path']);

$env = 'prod';
$sc = include __DIR__ . '/../src/container.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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
