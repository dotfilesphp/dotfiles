<?php

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\PluginInterface;
use Dotfiles\Core\Emitter;
use Dotfiles\Core\Events\ReloadBashConfigEvent;
use Dotfiles\Core\Util\Config;

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

    public function handleBashConfig(ReloadBashConfigEvent $event)
    {
        $config = Config::create();

        if($config->get('phpbrew.set_prompt')){
            $bashrc[] = 'export PHPBREW_SET_PROMPT=1';
        }
        if($config->get('phpbrew.rc_enable')){
            $bashrc[] = 'export PHPBREW_RC_ENABLE=1';
        }
        $bashrc[] = 'source $HOME/.phpbrew/bashrc';
        $event->addBashConfig($bashrc);
    }
}
