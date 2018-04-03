<?php

namespace Dotfiles\Core\Events;

class InstallEvent extends AbstractEvent
{
    const EVENT_NAME = 'dotfiles.install';

    public function getName()
    {
        return static::EVENT_NAME;
    }
}
