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

use Dotfiles\Core\DI\Parameters;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $config = new \Dotfiles\Core\DI\Parameters();
        $config['foo'] = 'bar';
        $this->assertTrue(isset($config['foo']));
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals('bar', $config->get('foo'));
        unset($config['foo']);
        $this->assertFalse(isset($config['foo']));
    }
}
