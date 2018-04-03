<?php

namespace Dotfiles\Plugins\Bash;

use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Config\Definition;
use Dotfiles\Core\Plugin;
use Dotfiles\Core\Emitter;
use Dotfiles\Plugins\Bash\Events\InstallListener;

class BashPlugin extends Plugin
{
    public function getName()
    {
        return "bash";
    }

    public function registerListeners(Emitter $emitter)
    {
        $emitter->addListener('dotfiles.install',new InstallListener());
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new Definition);
    }
}

