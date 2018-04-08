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

use Dotfiles\Core\Config\Definition;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class DefinitionTest.
 *
 * @covers \Dotfiles\Core\Config\Definition
 */
class DefinitionTest extends TestCase
{
    public function getTestConfigTreeBuilderData()
    {
        return array(
            array('machine_name', false),
            array('home_dir', getenv('HOME')),
            array('debug', false),
            array('base_dir', Toolkit::getBaseDir()),
            array('install_dir', '%dotfiles.home_dir%/.dotfiles'),
            array('log_dir', '%dotfiles.base_dir%/var/log'),
            array('cache_dir', '%dotfiles.base_dir%/var/cache'),
            array('temp_dir', sys_get_temp_dir().'/dotfiles/temp'),
            array('backup_dir', '%dotfiles.base_dir%/var/backup'),
            array('bin_dir', '%dotfiles.install_dir%/bin'),
            array('vendor_dir', '%dotfiles.install_dir%/vendor'),
        );
    }

    /**
     * @param string $key
     * @param string
     * @dataProvider getTestConfigTreeBuilderData
     */
    public function testGetConfigTreeBuilder(string $key, string $default): void
    {
        $definition = new Definition();
        $processor = new Processor();
        $config = $processor->process($definition->getConfigTreeBuilder()->buildTree(), array());

        $this->assertArrayHasKey($key, $config);
        $this->assertEquals($config[$key], $default);
    }
}
