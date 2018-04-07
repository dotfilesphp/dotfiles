<?php

namespace Dotfiles\Plugins\BashIt;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Plugin;
use Dotfiles\Plugins\BashIt\Config\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BashItPlugin extends Plugin
{
    public function getName()
    {
        return 'bash_it';
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new Definition());
    }

    public function configureContainer(ContainerBuilder $container, Config $config)
    {
        $locator = new FileLocator(__DIR__.'/Resources');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');
    }
}
