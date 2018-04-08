<?php

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\ApplicationFactory;
use PHPUnit\Framework\TestCase;

class ApplicationFactoryTest extends TestCase
{
    public function testCreateApplication()
    {
        chdir(__DIR__.'/fixtures/base');
        $factory = new ApplicationFactory();
        $app = $factory->createApplication();
        $this->assertTrue(class_exists('Dotfiles\Plugins\Foo\FooPlugin'));
        $this->assertTrue($factory->hasPlugin('foo'));
    }
}

