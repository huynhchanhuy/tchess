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

    public static function setUpBeforeClass()
    {
        if (empty(static::$sc)) {
            $config = include __DIR__ . '/../config/db-config-test.php';
            $env = 'test';

            static::$sc = include __DIR__ . '/../src/container.php';
        }

        // Getting schema tool.
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schema_tool = new SchemaTool($entityManager);

        // Drop all schemas.
        $schema_tool->dropSchema($metadatas);

        // Recreate schemas.
        $schema_tool->createSchema($metadatas);
    }
    /**
     * Creates new Client instance.
     *
     * @return Client A Client instance
     */
    protected static function createClient()
    {
        $client = new Client(static::$sc->get('framework'));

        return $client;
    }

    /**
     * Empty all tables.
     */
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
