<?php

declare(strict_types=1);

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
use PHPUnit\Framework\TestCase;

/**
 * Class ApplicationFactoryTest.
 *
 * @covers \Dotfiles\Core\ApplicationFactory
 */
class ApplicationFactoryTest extends TestCase
{
    static $cwd;
    static public function setUpBeforeClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();
        static::$cwd = getcwd();
        chdir(__DIR__.'/fixtures/base');
    }

    public function testCreateApplication(): void
    {

        $factory = new ApplicationFactory();
        $factory->boot();
        $this->assertTrue(class_exists('Dotfiles\Plugins\Foo\FooPlugin', true));
        $this->assertTrue($factory->hasPlugin('foo'));
    }

    public static function tearDownAfterClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDownAfterClass();
        chdir(static::$cwd);
    }
}
