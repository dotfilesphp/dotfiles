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

namespace Dotfiles\Plugins\PHPBrew\Config;

use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('phpbrew');
        $rootNode
            ->children()
                ->booleanNode('set_prompt')
                    ->defaultTrue()
                ->end()
                ->booleanNode('rc_enable')
                    ->defaultTrue()
                ->end()
                ->arrayNode('machines')
                    ->prototype('array')
                    ->children()
                            ->booleanNode('set_prompt')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('rc_enable')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    public function getBaseRootNode($node): void
    {
        $node
            ->children()
                ->booleanNode('set_prompt')
                    ->defaultTrue()
                ->end()
                ->booleanNode('rc_enable')
                    ->defaultTrue()
                ->end()
            ->end()
        ;
    }
}
