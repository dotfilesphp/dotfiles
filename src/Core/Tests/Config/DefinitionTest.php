<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Tests\Config;

use Dotfiles\Core\Config\Definition;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class DefinitionTest
 * @package Dotfiles\Core\Tests\Config
 * @covers \Dotfiles\Core\Config\Definition
 */
class DefinitionTest extends TestCase
{
    /**
     * @param string $key
     * @param string
     * @dataProvider getTestConfigTreeBuilderData
     */
    public function testGetConfigTreeBuilder(string $key,string $default)
    {
        $definition = new Definition();
        $processor = new Processor();
        $config = $processor->process($definition->getConfigTreeBuilder()->buildTree(),array());

        $this->assertArrayHasKey($key,$config);
        $this->assertEquals($config[$key],$default);
    }

    public function getTestConfigTreeBuilderData()
    {
        return [
            ['machine_name', false],
            ['home_dir',getenv('HOME')],
            ['debug',false],
            ['base_dir',Toolkit::getBaseDir()],
            ['install_dir','%dotfiles.home_dir%/.dotfiles'],
            ['log_dir','%dotfiles.base_dir%/var/log'],
            ['cache_dir','%dotfiles.base_dir%/var/cache'],
            ['temp_dir',sys_get_temp_dir().'/dotfiles/temp'],
            ['backup_dir','%dotfiles.base_dir%/var/backup'],
            ['bin_dir','%dotfiles.install_dir%/bin'],
            ['vendor_dir','%dotfiles.install_dir%/vendor']
        ];
    }
}
