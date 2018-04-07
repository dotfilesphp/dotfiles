<?php

namespace Dotfiles\Core;

use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PluginInterface
{
    /**
     * Returns the name of plugin.
     *
     * @return string
     */
    public function getName();

    /**
     * @param Config $config
     *
     * @return mixed
     */
    public function setupConfiguration(Config $config);

    public function configureContainer(ContainerBuilder $container, Config $config);
}
