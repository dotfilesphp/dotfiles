<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\Plugin;
use PHPUnit\Framework\TestCase;

class TestPlugin extends Plugin
{

}

class PluginTest extends TestCase
{
    public function testGetName()
    {
        $plugin = new TestPlugin();
        $this->assertEquals('test',$plugin->getName());
    }
}
