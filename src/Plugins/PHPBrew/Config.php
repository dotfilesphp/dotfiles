<?php

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\Config\ConfigInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Config implements ConfigInterface
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
