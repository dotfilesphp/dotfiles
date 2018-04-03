<?php

namespace Dotfiles\Plugins\Bash\Events;

use Dotfiles\Core\Events\AbstractEvent;

class ReloadBashConfigEvent extends AbstractEvent
{
    const EVENT_NAME = 'bash.reload_config';

    private $header = [];
    private $footer = [];

    public function getName()
    {
        return static::EVENT_NAME;
    }

    public function addHeaderConfig($contents)
    {
        if(!is_array($contents)){
            $contents = array($contents);
        }
        $this->header = array_merge($this->header,$contents);
        $this->getEmitter()->getLogger()->debug(
            "Added bash config",
            ['contents' => implode(PHP_EOL,$contents)]
        );
    }

    public function addFooterConfig($contents)
    {
        if(!is_array($contents)){
            $contents = array($contents);
        }
        $this->footer = array_merge($this->footer,$contents);
    }

    public function getBashConfig()
    {
        $config = array_merge($this->header,$this->footer);
        return implode(PHP_EOL,$config);
    }
}
