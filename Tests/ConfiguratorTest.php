<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
class ConfiguratorTest extends TestCase
{
    /**
     * @testdox  Configure service by key
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureKey(): void
    {
        $container = new Container();
        $container->set(
            'user',
            static function () {
                return new \stdClass();
            },
            true,
            true
        );

        $container->configure('user', function ($user) {
            $user->username = 'bob';
        });

        $container->configure('user', function ($user) {
            $user->email = 'bob@bob';
        });

        $user = $container->get('user');

        $this->assertSame('bob', $user->username);
        $this->assertSame('bob@bob', $user->email);
    }

    /**
     * @testdox  Configure service by fully qualified class name
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureClass(): void
    {
        $container = new Container();
        $container->set(
            'user_model',
            static function () {
                return new UserModel();
            },
            true,
            true
        );

        $db = new \stdClass();

        $container->configure(
            UserModel::class,
            function ($userModel) use ($db) {
                $userModel->setDatabase($db);
            }
        );

        $this->assertSame($db, $container->get('user_model')->getDatabase());
    }

    /**
     * @testdox  Configure service by fully qualified interface name
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureInterface(): void
    {
        $container = new Container();
        $container->set(
            'user_model',
            static function () {
                return new UserModel();
            },
            true,
            true
        );

        $db = new \stdClass();

        $container->configure(
            DatabaseAwareInterface::class,
            function ($userModel) use ($db) {
                $userModel->setDatabase($db);
            }
        );

        $this->assertSame($db, $container->get('user_model')->getDatabase());
    }

    /**
     * @testdox  Configure service by alias
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureAlias(): void
    {
        $container = new Container();
        $container->set(
            'user',
            static function () {
                return new \stdClass();
            },
            true,
            true
        );

        $container->alias('alice', 'user');

        $container->configure('alice', function ($user) {
            $user->username = 'alice';
        });

        $this->assertSame('alice', $container->get('user')->username);
    }

    /**
     * @testdox  Configure shared service only once
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureSharedOnce(): void
    {
        $container = new Container();
        $container->set(
            'user',
            static function () {
                return new \stdClass();
            },
            true,
            true
        );

        $called = 0;

        $container->configure(\stdClass::class, function ($user) use (&$called) {
            $called++;
            $user->username = 'alice';
        });

        $user1 = $container->get('user');
        $user2 = $container->get('user');
        $user3 = $container->get('user');

        $this->assertEquals(1, $called);
    }

    /**
     * @testdox  Configure not shared service every time
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testConfigureNotShared(): void
    {
        $container = new Container();
        $container->set(
            'user',
            static function () {
                return new \stdClass();
            },
            false,
            true
        );

        $called = 0;

        $container->configure(\stdClass::class, function ($user) use (&$called) {
            $called++;
            $user->username = 'alice';
        });

        $user1 = $container->get('user');
        $user2 = $container->get('user');
        $user3 = $container->get('user');

        $this->assertEquals(3, $called);
    }
}
