<?php

namespace Vectorface\SnappyRouterTests\Config;

use Exception;
use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Config\Config;

/**
 * Tests the config wrapper class for the router.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class ConfigTest extends TestCase
{
    /**
     * Demonstrates basic usage of the Config wrapper class.
     *
     * @throws Exception
     */
    public function testSynopsis()
    {
        $arrayConfig = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        // initialize the class from an array
        $config = new Config($arrayConfig);

        // assert all the keys and values match
        foreach ($arrayConfig as $key => $value) {
            // using the array accessor syntax
            $this->assertEquals($value, $config[$key]);
            // using the get method
            $this->assertEquals($value, $config->get($key));
        }

        $config['key3'] = 'value3';
        $this->assertEquals('value3', $config['key3']);

        $config->set('key4', 'value4');
        $this->assertEquals('value4', $config['key4']);

        unset($config['key4']);
        $this->assertNull($config['key4']); // assert we unset the value
        $this->assertEquals(false, $config->get('key4', false)); // test default values

        unset($config['key3']);
        $this->assertEquals($arrayConfig, $config->toArray());
    }

    /**
     * Test that we cannot append to the config class like we would a normal array.
     */
    public function testExceptionThrownWhenConfigIsAppended()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Config values must contain a key.");

        $config = new Config([]);
        $config[] = 'new value';
    }
}
