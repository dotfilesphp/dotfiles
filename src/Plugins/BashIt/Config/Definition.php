<?php


namespace Dotfiles\Plugins\BashIt\Config;


use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements DefinitionInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('bash_it');
        $root
            ->children()
                ->scalarNode('theme')
                    ->defaultValue('atomic')
                ->end()
                ->scalarNode('irc_client')
                    ->defaultValue('irssi')
                ->end()
                ->scalarNode('todo')
                    ->defaultValue('t')
                ->end()
                ->booleanNode('scm_check')
                    ->defaultTrue()
                ->end()
            ->end()
        ;
        return $builder;
    }
}
