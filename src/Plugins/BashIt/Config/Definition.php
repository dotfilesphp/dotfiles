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
                ->scalarNode('theme_name')
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
                ->scalarNode('git_hosting')
                    ->defaultValue('git@domain.com')
                ->end()
                ->booleanNode('automatic_reload')
                    ->defaultTrue()
                ->end()
                ->arrayNode('theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('show_clock_char')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('show_clock')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('clock_color')
                            ->defaultValue('$normal')
                        ->end()
                        ->scalarNode('clock_format')
                            ->defaultValue('%H:%M:%S')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $builder;
    }
}
