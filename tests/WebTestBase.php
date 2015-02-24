<?php

namespace Tchess\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\Client;

/**
 * Tchess web test base class.
 */
class WebTestBase extends \PHPUnit_Framework_TestCase
{

    public static $sc;
    public static $client;

    public static function setUpBeforeClass()
    {
        if (empty(static::$sc)) {
            // @todo - Use in memory sqlite to speed up the test.
            $config = include __DIR__ . '/../config/db-config-test.php';
            $env = 'test';

            static::$sc = require_once __DIR__ . '/../src/container.php';
            static::$client = new Client(static::$sc->get('framework'));
        }

        // getting objects.
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schema_tool = new SchemaTool($entityManager);

        // drop all schemas.
        $schema_tool->dropSchema($metadatas);

        // recreate schemas
        $schema_tool->createSchema($metadatas);
    }

    public function tearDown()
    {
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            $entityManager->createQuery('DELETE FROM ' . $metadata->getName())->execute();
        }

        // Delete doctrine's cache.
        $entityManager->clear();
    }

}
