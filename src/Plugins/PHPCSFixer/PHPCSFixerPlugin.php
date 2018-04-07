<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\PHPCSFixer;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Plugin;
use Dotfiles\Plugins\PHPCSFixer\Config\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PHPCSFixerPlugin extends Plugin
{
    public function getName()
    {
        return 'phpcs';
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
