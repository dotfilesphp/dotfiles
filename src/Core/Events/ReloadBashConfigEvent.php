<?php

namespace Dotfiles\Core\Events;

class ReloadBashConfigEvent extends AbstractEvent
{
    const EVENT_NAME = 'bash.reload_config';

    private $bashrc = [];

    public function getName()
    {
        return static::EVENT_NAME;
    }

    public function addBashConfig(array $contents)
    {
        $this->bashrc = array_merge($this->bashrc,$contents);
        $this->getEmitter()->getLogger()->debug("Added bash config:\n".implode(PHP_EOL,$contents));
    }

    public function getBashConfig()
    {
        return implode(PHP_EOL,$this->bashrc);
    }
}
