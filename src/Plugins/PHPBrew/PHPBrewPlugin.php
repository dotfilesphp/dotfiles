<?php

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\PluginInterface;
use Dotfiles\Core\Emitter;
use Dotfiles\Plugins\Bash\Events\ReloadBashConfigEvent;
use Dotfiles\Core\Config\Config;

class PHPBrewPlugin implements PluginInterface
{
    public function getName()
    {
        return "PHPBrew";
    }

    public function registerListeners(Emitter $emitter)
    {
        $emitter->addListener(ReloadBashConfigEvent::EVENT_NAME,[$this,'handleBashConfig']);
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new ConfigDefinition());
    }

    public function handleBashConfig(ReloadBashConfigEvent $event)
    {
        $config = $event->getConfig();
        if($config->get('phpbrew.set_prompt')){
            $event->addHeaderConfig('export PHPBREW_SET_PROMPT=1');
        }
        if($config->get('phpbrew.rc_enable')){
            $event->addHeaderConfig('export PHPBREW_RC_ENABLE=1');
        }
        $event->addFooterConfig('source $HOME/.phpbrew/bashrc');
    }
}
