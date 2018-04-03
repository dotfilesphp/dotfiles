<?php

namespace Dotfiles\Plugins\Bash;

use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Config\Definition;
use Dotfiles\Core\Plugin;
use Dotfiles\Core\Emitter;
use Dotfiles\Plugins\Bash\Event\InstallListener;
use Dotfiles\Core\Event\InstallEvent;

class BashPlugin extends Plugin
{
    public function getName()
    {
        return "bash";
    }

    public function registerListeners(Emitter $emitter)
    {
        $emitter->addListener(InstallEvent::EVENT_NAME,new InstallListener());
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new Definition);
    }
}

