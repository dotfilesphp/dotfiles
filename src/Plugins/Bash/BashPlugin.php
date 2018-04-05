<?php

namespace Dotfiles\Plugins\Bash;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Config\Definition;
use Dotfiles\Core\Plugin;

class BashPlugin extends Plugin
{
    public function getName()
    {
        return "bash";
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new Definition);
    }

    public function configureContainer(ContainerBuilder $container, Config $config)
    {
        $locator = new FileLocator(__DIR__.'/Resources');
        $loader = new YamlFileLoader($container,$locator);
        $loader->load('services.yaml');
    }
}

