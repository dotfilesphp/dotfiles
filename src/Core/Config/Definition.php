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
        $root = $builder->root('dotfiles');
        $root
            ->children()
                ->scalarNode('machine_name')
                    ->defaultValue(getenv('DOTFILES_MACHINE_NAME'))
                ->end()
                ->scalarNode('home_dir')
                    ->defaultValue(getenv('HOME'))
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
                    ->defaultValue('%dotfiles.base_dir%/var/log')
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%dotfiles.base_dir%/var/cache')
                ->end()
                ->scalarNode('temp_dir')
                    ->defaultValue($tempDir)
                ->end()
                ->scalarNode('backup_dir')
                    ->defaultValue('%dotfiles.base_dir%/var/backup')
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
