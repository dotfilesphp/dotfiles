<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Dotfiles\Core\DI;

use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(Config::class);

        $taggedServices = $container->findTaggedServiceIds('dotfiles.plugin');

        foreach($taggedServices as $id => $taggedService)
        {

        }
    }

}
