<?php

namespace Dotfiles\Core;

use Dotfiles\Core\Config\Config;

interface PluginInterface
{
    /**
     * Returns the name of plugin
     *
     * @return string
     */
    public function getName();

    /**
     * Register event listeners
     * @param Emitter $emitter
     */
    public function registerListeners(Emitter $emitter);

    /**
     * @param Config $config
     * @return mixed
     */
    public function setupConfiguration(Config $config);

    public function configureContainer(ContainerInterface $container);
}
