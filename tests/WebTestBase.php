<?php

namespace Tchess\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Tchess web test base class.
 */
class WebTestBase extends \PHPUnit_Framework_TestCase
{

    public static $sc;

    public static function setUpBeforeClass()
    {
        // @todo - Use in memory sqlite to speed up the test.
        $config = array(
            'driver' => 'pdo_sqlite',
            'path' => '%root_dir%/db/sqlite_test.db',
            'charset' => 'UTF-8',
        );
        $config['path'] = str_replace('%root_dir%', __DIR__ . '/..', $config['path']);

        $env = 'test';
        static::$sc = include __DIR__ . '/../src/container.php';

        // getting objects.
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schema_tool = new SchemaTool($entityManager);

        // drop all schemas.
        $schema_tool->dropSchema($metadatas);

        // recreate schemas
        $schema_tool->createSchema($metadatas);
    }

    protected function getRequest($path, $method = 'GET', $parameters = array(), $session = null)
    {
        $request = Request::create($path, $method, $parameters);

        if (empty($session)) {
          $session = new Session(new MockArraySessionStorage());
          $session->start();
        }

        $request->setSession($session);

        static::$sc->get('context')->fromRequest($request);

        return $request;
    }

}
