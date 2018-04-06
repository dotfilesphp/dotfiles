<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Dotfiles\Core\Util\Toolkit;
class Definition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $defInstallDir = getenv('HOME').DIRECTORY_SEPARATOR.'.dotfiles';
        $baseDir = Toolkit::getBaseDir();
        $root = $builder->root('dotfiles');
        $root
            ->children()
                ->booleanNode('debug')
                    ->defaultFalse()
                ->end()
                ->scalarNode('base_dir')
                    ->defaultValue($baseDir)
                ->end()
                ->scalarNode('log_dir')
                    ->defaultValue('%dotfiles.base_dir%/var/log')
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%dotfiles.base_dir%/var/cache')
                ->end()
                ->scalarNode('temp_dir')
                    ->defaultValue('%dotfiles.base_dir%/var/temp')
                ->end()
                ->scalarNode('install_dir')
                    ->defaultValue($defInstallDir)
                ->end()
            ->end()
        ;

        return $builder;
    }
}
