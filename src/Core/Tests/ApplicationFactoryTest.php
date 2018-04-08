<?php

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\ApplicationFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class ApplicationFactoryTest
 *
 * @package Dotfiles\Core\Tests
 * @covers \Dotfiles\Core\ApplicationFactory
 */
class ApplicationFactoryTest extends TestCase
{
    public function testCreateApplication()
    {
        chdir(__DIR__.'/fixtures/base');
        $factory = new ApplicationFactory();
        $factory->boot();
        $this->assertTrue(class_exists('Dotfiles\Plugins\Foo\FooPlugin', true));
        $this->assertTrue($factory->hasPlugin('foo'));
    }
}

