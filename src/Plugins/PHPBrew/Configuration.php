<?php

declare(strict_types=1);

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements DefinitionInterface
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

    private function getBaseRootNode($node): void
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
