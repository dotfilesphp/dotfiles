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

class Definition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $defInstallDir = getenv('HOME').DIRECTORY_SEPARATOR.'.dotfiles';
        $root = $builder->root('dotfiles');
        $root
            ->children()
                ->scalarNode('install_dir')
                    ->defaultValue($defInstallDir)
                ->end()
            ->end()
        ;

        return $builder;
    }
}
