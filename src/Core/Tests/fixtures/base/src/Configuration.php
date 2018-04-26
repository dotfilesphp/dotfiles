<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Foo;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('test');
        $root
            ->children()
                ->scalarNode('foo')
                    ->defaultValue('bar')
                ->end()
                ->scalarNode('hello')
                    ->defaultValue('world')
                ->end()
                ->scalarNode('some_key')
                    ->defaultValue('should-override')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
