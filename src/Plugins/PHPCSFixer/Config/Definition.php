<?php

namespace Dotfiles\Plugins\PHPCSFixer\Config;

use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements DefinitionInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('phpcs');
        $root
            ->children()
                ->scalarNode('file_name')
                    ->defaultValue('phpcs')
                ->end()
            ->end()
        ;

        return $builder;
    }
}
