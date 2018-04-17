<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\ApplicationFactory;
use Dotfiles\Core\Tests\Helper\BaseTestCase;

class TestApplicationFactory extends ApplicationFactory
{
    protected function getContainerId()
    {
        return crc32(__FILE__);
    }
}

/**
 * Class ApplicationFactoryTest.
 *
 * @covers \Dotfiles\Core\ApplicationFactory
 */
class ApplicationFactoryTest extends BaseTestCase
{
    public function testConfiguration()
    {
        $factory = $this->getFactory();
        $container = $factory->getContainer();

        $this->assertTrue($container->hasParameter('test.foo'));
        $this->assertEquals('bar', $container->getParameter('test.foo'));
        $this->assertEquals('world', $container->getParameter('test.hello'));
        $this->assertEquals('hello world', $container->getParameter('test.some_key'));
    }

    public function testLoadPlugin()
    {
        $factory = $this->getFactory();

        $container = $factory->getContainer();
        $this->assertTrue($container->has('dotfiles.app'));
        $this->assertTrue($container->has('foo.hello'));
        $this->assertTrue($factory->hasPlugin('test'));
    }

    private function getFactory()
    {
        static $factory;
        if (null === $factory) {
            $this->boot();
            $this->createBackupDirMock(__DIR__.'/fixtures/base');
            $factory = new TestApplicationFactory();
            $factory->boot();
        }

        return $factory;
    }
}
