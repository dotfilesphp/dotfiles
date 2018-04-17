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

use Dotfiles\Core\Tests\fixtures\TestPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PluginTest.
 *
 * @covers \Dotfiles\Core\Plugin
 */
class PluginTest extends TestCase
{
    public function testGetName(): void
    {
        $builder = new ContainerBuilder();
        $plugin = new TestPlugin();
        $plugin->load(array(), $builder);
        $builder->compile(true);

        $this->assertEquals('test', $plugin->getName());
        $this->assertTrue($builder->has('test.plugin'));
        $this->assertTrue($builder->hasParameter('test.foo'));
        $this->assertTrue($builder->hasParameter('test.hello'));
    }
}
