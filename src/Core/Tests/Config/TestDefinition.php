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


use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class TestDefinition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('test');
        $root
            ->children()
                ->scalarNode('foo')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('hello')
                    ->defaultValue('default')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
