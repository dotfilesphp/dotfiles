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

use Dotfiles\Core\Configuration;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class ConfigurationTest.
 *
 * @covers \Dotfiles\Core\Config\Definition
 */
class ConfigurationTest extends TestCase
{
    public function getTestConfigTreeBuilderData()
    {
        return array(
            array('machine_name','%env(DOTFILES_MACHINE_NAME)%'),
            array('home_dir', '%env(DOTFILES_HOME_DIR)%'),
            array('debug', '%env(DOTFILES_DEBUG)%'),
            array('base_dir', Toolkit::getBaseDir()),
            array('install_dir', '%env(DOTFILES_INSTALL_DIR)%'),
            array('log_dir', '%env(DOTFILES_LOG_DIR)%'),
            array('cache_dir', '%env(DOTFILES_CACHE_DIR)%'),
            array('temp_dir', '%env(DOTFILES_TEMP_DIR)%'),
            array('backup_dir', '%env(DOTFILES_BACKUP_DIR)%'),
            array('bin_dir', '%env(DOTFILES_INSTALL_DIR)%/bin'),
            array('vendor_dir', '%env(DOTFILES_INSTALL_DIR)%/vendor'),
        );
    }

    /**
     * @param string $key
     * @param string
     * @dataProvider getTestConfigTreeBuilderData
     */
    public function testGetConfigTreeBuilder(string $key, string $default): void
    {
        $definition = new Configuration();
        $processor = new Processor();
        $config = $processor->process($definition->getConfigTreeBuilder()->buildTree(), array());

        $this->assertArrayHasKey($key, $config);
        $this->assertEquals($default, $config[$key]);
    }
}
