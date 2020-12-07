<?php

namespace Vectorface\SnappyRouterTests\Plugin;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Plugin\AbstractPlugin;

class TestPluginTest extends TestCase
{
    /**
     * A demonstrate of a simple test plugin.
     */
    public function testSynopsis()
    {
        $options = [];
        $plugin = new TestPlugin($options);

        $this->assertEquals(
            AbstractPlugin::PRIORITY_DEFAULT,
            $plugin->getExecutionOrder()
        );

        $plugin->setWhitelist(
            [
                'TestController' => AbstractPlugin::ALL_ACTIONS
            ]
        );
        $this->assertTrue(
            $plugin->supportsControllerAndAction(
                'TestController',
                'someAction'
            )
        );
    }

    /**
     * Tests the supportsControllerAndAction methods.
     */
    public function testSupportsControllerAndAction()
    {
        $plugin = new TestPlugin([]);

        // no lists yet, so plugin supports everything
        $this->assertTrue(
            $plugin->supportsControllerAndAction(
                'TestController',
                'anyAction'
            )
        );

        // set a whitelist
        $plugin->setWhitelist(
            [
                'TestController'    => AbstractPlugin::ALL_ACTIONS,
                'AnotherController' => [
                    'specificAction'
                ]
            ]
        );
        // all actions enabled for this controller
        $this->assertTrue(
            $plugin->supportsControllerAndAction(
                'TestController',
                'anyAction'
            )
        );
        // specific action enabled
        $this->assertTrue(
            $plugin->supportsControllerAndAction(
                'AnotherController',
                'specificAction'
            )
        );
        // controller is missing from whitelist
        $this->assertFalse(
            $plugin->supportsControllerAndAction(
                'MissingController',
                'anyAction'
            )
        );
        // action is missing from whitelist
        $this->assertFalse(
            $plugin->supportsControllerAndAction(
                'AnotherController',
                'differentAction'
            )
        );

        // now the reverse logic for the blacklist
        $plugin->setBlacklist(
            [
                'TestController' => [
                    'bannedAction'
                ],
                'BannedController' => AbstractPlugin::ALL_ACTIONS
            ]
        );
        // controller is missing from blacklist
        $this->assertTrue(
            $plugin->supportsControllerAndAction(
                'MissingController',
                'anyAction'
            )
        );
        // action is blacklisted specifically
        $this->assertFalse(
            $plugin->supportsControllerAndAction(
                'TestController',
                'bannedAction'
            )
        );
        // all actions for the controller are banned
        $this->assertFalse(
            $plugin->supportsControllerAndAction(
                'BannedController',
                'anyAction'
            )
        );
    }
}
