<?php

namespace Dotfiles\Core;

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
}
