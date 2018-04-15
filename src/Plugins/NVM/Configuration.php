<?php

namespace Dotfiles\Plugins\NVM;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for NVM
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder();
        
        $root = $builder->root('nvm');
        $root
            ->children()
                ->scalarNode('install_dir')
                    ->defaultValue('%dotfiles.vendor_dir%/.nvm')
                ->end()
            ->end()
        ;
        return $builder;
    }
}
