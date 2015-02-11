<?php

namespace Tchess\Tests;

/**
 * Tchess unit test base class.
 */
class UnitTestBase extends \PHPUnit_Framework_TestCase
{

    public static $sc;
    protected $serializer;
    protected $validator;

    public static function setUpBeforeClass()
    {
        $config = array();
        $env = 'test';
        static::$sc = include __DIR__ . '/../src/container.php';
    }

    protected function setUp()
    {
        $this->serializer = static::$sc->get('serializer');
        $this->validator = static::$sc->get('validator');
    }

}
