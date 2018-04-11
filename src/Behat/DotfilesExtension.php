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

namespace Dotfiles\Behat;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DotfilesExtension implements Extension
{
    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('command_prefix')
                    ->defaultValue('dotfiles ')
                ->end()
            ->end()
        ;
    }

    public function getConfigKey()
    {
        return 'dotfiles';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
        // TODO: Implement initialize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $locator = new FileLocator(__DIR__.'/Resources');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');

        $container->setParameter('dotfiles.command_prefix', $config['command_prefix']);
    }

    public function process(ContainerBuilder $container): void
    {
        // TODO: Implement process() method.
    }
}
