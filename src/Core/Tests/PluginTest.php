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

use Dotfiles\Core\Plugin;
use PHPUnit\Framework\TestCase;

class TestPlugin extends Plugin
{
}

/**
 * Class PluginTest.
 *
 * @covers \Dotfiles\Core\Plugin
 */
class PluginTest extends TestCase
{
    public function testGetName(): void
    {
        $plugin = new TestPlugin();
        $this->assertEquals('test', $plugin->getName());
    }
}
