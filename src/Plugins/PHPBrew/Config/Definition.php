<?php

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

    public function getBaseRootNode($node)
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
