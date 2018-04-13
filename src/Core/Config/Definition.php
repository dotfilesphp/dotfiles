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

namespace Dotfiles\Core\Config;

use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $baseDir = Toolkit::getBaseDir();
        $tempDir = sys_get_temp_dir().'/dotfiles/temp';

        $backupDir = getenv('DOTFILES_BACKUP_DIR');
        $varDir = $backupDir.'/var';
        if (false === $backupDir) {
            $varDir = sys_get_temp_dir().'/dotfiles/var';
            $backupDir = sys_get_temp_dir().'/dotfiles/backup';
        }
        $homeDir = getenv('DOTFILES_HOME_DIR');

        if ('dev' === getenv('DOTFILES_ENV')) {
            //$homeDir = sys_get_temp_dir().'/dotfiles/home';
            //$varDir = sys_get_temp_dir().'/dotfiles/var';
        }

        $root = $builder->root('dotfiles');
        $root
            ->children()
                ->scalarNode('backup_dir')
                    ->defaultValue($backupDir)
                ->end()
                ->scalarNode('env')
                    ->defaultValue(getenv('DOTFILES_ENV'))
                ->end()
                ->booleanNode('dry_run')
                    ->defaultTrue()
                ->end()
                ->scalarNode('machine_name')
                    ->defaultValue(getenv('DOTFILES_MACHINE_NAME'))
                ->end()
                ->scalarNode('home_dir')
                    ->defaultValue($homeDir)
                ->end()
                ->booleanNode('debug')
                    ->defaultFalse()
                ->end()
                ->scalarNode('base_dir')
                    ->defaultValue($baseDir)
                ->end()
                ->scalarNode('install_dir')
                    ->defaultValue('%dotfiles.home_dir%/.dotfiles')
                ->end()
                ->scalarNode('log_dir')
                    ->defaultValue($varDir.'/log')
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue($varDir.'/cache')
                ->end()
                ->scalarNode('temp_dir')
                    ->defaultValue($tempDir)
                ->end()
                ->scalarNode('bin_dir')
                    ->defaultValue('%dotfiles.install_dir%/bin')
                ->end()
                ->scalarNode('vendor_dir')
                    ->defaultValue('%dotfiles.install_dir%/vendor')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
