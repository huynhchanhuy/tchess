<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$sc = include __DIR__ . '/../src/container.php';

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
