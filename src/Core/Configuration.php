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

namespace Dotfiles\Core;

use Dotfiles\Core\Config\DefinitionInterface;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('dotfiles');
        $root
            ->children()
                ->scalarNode('env')
                    ->defaultValue('%env(DOTFILES_ENV)%')
                ->end()
                ->booleanNode('debug')
                    ->defaultValue('%env(DOTFILES_DEBUG)%')
                ->end()
                ->booleanNode('phar_mode')
                    ->defaultValue(getenv('DOTFILES_PHAR_MODE'))
        ->end()
        ->scalarNode('dry_run')
            ->defaultFalse()
        ->end()
                ->scalarNode('machine_name')
                    ->defaultValue('%env(DOTFILES_MACHINE_NAME)%')
                ->end()
                ->scalarNode('backup_dir')
                    ->defaultValue('%env(DOTFILES_BACKUP_DIR)%')
                ->end()
                ->scalarNode('log_dir')
                    ->defaultValue('%env(DOTFILES_LOG_DIR)%')
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%env(DOTFILES_CACHE_DIR)%')
                ->end()
                ->scalarNode('home_dir')
                    ->defaultValue('%env(DOTFILES_HOME_DIR)%')
                ->end()
                ->scalarNode('base_dir')
                    ->defaultValue(Toolkit::getBaseDir())
                ->end()
                ->scalarNode('temp_dir')
                    ->defaultValue('%env(DOTFILES_TEMP_DIR)%')
                ->end()
                ->scalarNode('install_dir')
                    ->defaultValue('%env(DOTFILES_INSTALL_DIR)%')
                ->end()
                ->scalarNode('bin_dir')
                    ->defaultValue('%env(DOTFILES_INSTALL_DIR)%/bin')
                ->end()
                ->scalarNode('vendor_dir')
                    ->defaultValue('%env(DOTFILES_INSTALL_DIR)%/vendor')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
