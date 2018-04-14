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

namespace Dotfiles\Core\Tests\Config;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\DefinitionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\NodeInterface;

class ConfigTest extends TestCase
{

    public function testArrayAccess(): void
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertTrue(isset($config['foo']));
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals('bar', $config->get('foo'));
        unset($config['foo']);
        $this->assertFalse(isset($config['foo']));
    }
}
